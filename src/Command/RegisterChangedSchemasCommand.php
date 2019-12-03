<?php

namespace Jobcloud\SchemaConsole\Command;

use AvroSchema;
use AvroSchemaParseException;
use GuzzleHttp\Exception\RequestException;
use Jobcloud\KafkaSchemaRegistryClient\Interfaces\KafkaSchemaRegistryApiClientInterface;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class RegisterChangedSchemasCommand extends AbstractSchemaCommand
{

    /**
     * @var integer
     */
    private $maxRetries;

    /**
     * @var bool
     */
    private $abortRegister = false;

    /**
     * @param KafkaSchemaRegistryApiClientInterface $schemaRegistryApi
     * @param integer           $maxRetries
     */
    public function __construct(KafkaSchemaRegistryApiClientInterface $schemaRegistryApi, int $maxRetries = 10)
    {
        parent::__construct($schemaRegistryApi);
        $this->maxRetries = $maxRetries;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:register:changed')
            ->setDescription('Register all changed schemas from a path')
            ->setHelp('Register all changed schemas from a path')
            ->addArgument('schemaDirectory', InputArgument::REQUIRED, 'Path to avro schema directory');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     * @throws RequestException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);

        /** @var string $directory */
        $directory = $input->getArgument('schemaDirectory');
        $avroFiles = SchemaFileHelper::getAvroFiles($directory);

        $retries = 0;

        $failed = [];
        $succeeded = [];

        while (false === $this->abortRegister) {
            if (false === $this->registerFiles($avroFiles, $io, $failed, $succeeded)) {
                return 1;
            }

            $this->abortRegister = (0 === count($failed)) || ($this->maxRetries === ++$retries);
        }

        if (isset($failed) && 0 !== count($failed)) {
            $io->warning('Failed schemas the following schemas:');
            $io->listing($failed);
        }

        if (isset($succeeded) && 0 !== count($succeeded)) {
            $io->success('Succeeded registering the following schemas:');
            $io->listing(array_map(static function ($item) {
                return sprintf('%s with new version: %s', $item['name'], $item['version']);
            }, $succeeded));
        }

        return (int) (isset($failed) && 0 !== count($failed));
    }

    /**
     * @param string $schemaName
     * @param string $localSchema
     * @return boolean
     */
    protected function isAlreadyRegistered(
        string $schemaName,
        string $localSchema
    ): bool {
        $version = null;

        try {
            $version = $this->schemaRegistryApi->getVersionForSchema($schemaName, $localSchema);
        } catch (Throwable $e) {
        }

        return null !== $version;
    }

    /**
     * @param array        $avroFiles
     * @param SymfonyStyle $io
     * @param array        $failed
     * @param array        $succeeded
     * @return boolean
     */
    private function registerFiles(
        array $avroFiles,
        SymfonyStyle $io,
        array &$failed = [],
        array &$succeeded = []
    ): bool {
        foreach ($avroFiles as $schemaName => $avroFile) {
            /** @var string $fileContents */
            $fileContents = file_get_contents($avroFile);

            /** @var array $jsonDecoded */
            $jsonDecoded = json_decode($fileContents);

            /** @var string $localSchema */
            $localSchema = json_encode($jsonDecoded);

            $latestVersion = $this->schemaRegistryApi->getLatestSubjectVersion($schemaName);

            if (null !== $latestVersion) {
                if (true === $this->isAlreadyRegistered($schemaName, $localSchema)) {
                    $io->writeln(sprintf('Schema %s has been skipped (no change)', $schemaName));
                    continue;
                }

                if (false === $this->schemaRegistryApi->checkSchemaCompatibilityForVersion($localSchema, $schemaName)) {
                    $io->error(sprintf('Schema %s has an incompatible change', $schemaName));
                    return false;
                }
            }

            try {
                $schema = AvroSchema::parse($localSchema);
            } catch (AvroSchemaParseException $e) {
                $io->writeln(sprintf('Skipping %s for now because %s', $schemaName, $e->getMessage()));
                $failed[$schemaName] = $schemaName;
                continue;
            }

            $this->schemaRegistryApi->registerNewSchemaVersion($schemaName, $schema);

            $succeeded[$schemaName] = [
                'name' => $schemaName,
                'version' => $this->schemaRegistryApi->getVersionForSchema($schemaName, $schema),
            ];
            unset($failed[$schemaName]);

            $io->writeln(sprintf('Successfully registered new version of schema %s', $schemaName));
        }

        return true;
    }
}
