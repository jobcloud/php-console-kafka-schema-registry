<?php

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\RequestException;
use Jobcloud\SchemaConsole\Helper\Avro;
use Jobcloud\SchemaConsole\SchemaRegistryApi;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param SchemaRegistryApi $schemaRegistryApi
     * @param integer           $maxRetries
     */
    public function __construct(SchemaRegistryApi $schemaRegistryApi, int $maxRetries = 10)
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
        /** @var string $directory */
        $directory = $input->getArgument('schemaDirectory');
        $avroFiles = $this->getAvroFiles($directory);

        $retries = 0;

        while (false === $this->abortRegister) {
            $failed = [];

            if (false === $this->registerFiles($avroFiles, $output, $failed)) {
                return 1;
            }

            $this->abortRegister = (0 === count($failed)) || ($this->maxRetries === ++$retries);
        }

        if (isset($failed) && 0 !== count($failed)) {
            $output->writeln(sprintf('Was unable to register the following schemas %s', implode(', ', $failed)));
            return 1;
        }

        return 0;
    }

    /**
     * @param string $directory
     * @return array
     */
    protected function getAvroFiles(string $directory): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $directory,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        $files = [];

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (Avro::FILE_EXTENSION !== $file->getExtension()) {
                continue;
            }

            $files[$file->getBasename('.' . Avro::FILE_EXTENSION)] = $file->getRealPath();
        }

        return $files;
    }

    /**
     * @param string $schemaName
     * @param string $localSchema
     * @param string $latestVersion
     * @return boolean
     */
    protected function isLocalSchemaCompatible(
        string $schemaName,
        string $localSchema,
        string $latestVersion
    ): bool {
        return $this->schemaRegistryApi->checkSchemaCompatibilityForVersion(
            $localSchema,
            $schemaName,
            $latestVersion
        );
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
            $version = $this->schemaRegistryApi->getVersionForSchema(
                $schemaName,
                $localSchema
            );
        } catch (\Throwable $e) {
        }

        return null !== $version;
    }

    /**
     * @param array           $avroFiles
     * @param OutputInterface $output
     * @param array           $failed
     * @return boolean
     */
    private function registerFiles(array $avroFiles, OutputInterface $output, array &$failed = []): bool
    {
        foreach ($avroFiles as $schemaName => $avroFile) {
            /** @var string $fileContents */
            $fileContents = file_get_contents($avroFile);

            /** @var array $jsonDecoded */
            $jsonDecoded = json_decode($fileContents);

            /** @var string $localSchema */
            $localSchema = json_encode($jsonDecoded);

            $latestVersion = $this->schemaRegistryApi->getLatestSchemaVersion($schemaName);

            if (null !== $latestVersion) {
                if (true === $this->isAlreadyRegistered($schemaName, $localSchema)) {
                    $output->writeln(sprintf('Schema %s has been skipped (no change)', $schemaName));
                    continue;
                }

                if (false === $this->isLocalSchemaCompatible($schemaName, $localSchema, $latestVersion)) {
                    $output->writeln(sprintf('Schema %s has an incompatible change', $schemaName));
                    return false;
                }
            }

            try {
                $schema = \AvroSchema::parse($localSchema);
            } catch (\AvroSchemaParseException $e) {
                $output->writeln(sprintf('Skipping %s for now because %s', $schemaName, $e->getMessage()));
                $failed[] = $avroFile;
                continue;
            }

            $this->schemaRegistryApi->createNewSchemaVersion($schema, $schemaName);

            $output->writeln(sprintf('Successfully registered new version of schema %s', $schemaName));
        }

        return true;
    }
}
