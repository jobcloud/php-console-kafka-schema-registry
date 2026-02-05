<?php

namespace Jobcloud\SchemaConsole\Command;

use AvroSchema;
use AvroSchemaParseException;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RegisterChangedSchemasCommand extends AbstractSchemaCommand
{
    private bool $abortRegister = false;

    public function __construct(
        KafkaSchemaRegistryApiClientInterface $schemaRegistryApi,
        private int $maxRetries = 10
    ) {
        parent::__construct($schemaRegistryApi);
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:register:changed')
            ->setDescription('Register all changed schemas from a path')
            ->setHelp('Register all changed schemas from a path')
            ->addArgument('schemaDirectory', InputArgument::REQUIRED, 'Path to avro schema directory')
            ->addOption(
                'useSchemaVersioning',
                null,
                InputOption::VALUE_NONE,
                'Register schemas with multiple versions (e.g. ch.jobcloud.namespace.schema.1.avsc)'
            );
    }

    #[\Override]
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $directory */
        $directory = $input->getArgument('schemaDirectory');
        $avroFiles = SchemaFileHelper::getAvroFiles($directory);

        $retries = 0;

        $failed = [];
        $succeeded = [];

        $useSchemaVersioning = (bool) $input->getOption('useSchemaVersioning');

        $successMessage = '%s with new version: %s';
        if ($useSchemaVersioning) {
            natsort($avroFiles);
            $successMessage = '%s with new versions, the latest being: %s';
        }

        while (false === $this->abortRegister) {
            if (false === $this->registerFiles($avroFiles, $io, $failed, $succeeded, $useSchemaVersioning)) {
                return 1;
            }

            $this->abortRegister = ([] === $failed) || ($this->maxRetries === ++$retries);
        }

        if ([] !== $failed) {
            $io->warning('Failed schemas the following schemas:');
            $io->listing($failed);
        }

        if ([] !== $succeeded) {
            $io->success('Succeeded registering the following schemas:');
            $io->listing(
                array_map(
                    static fn(array $item): string => sprintf(
                        $successMessage,
                        $item['name'],
                        $item['version']
                    ),
                    $succeeded
                )
            );
        }

        return count($failed) ? 1 : 0;
    }

    /**
     * @param array<string, string> $avroFiles
     * @param array<string, string> $failed
     * @param array<string, array{name: string, version: string|null}> $succeeded
     */
    private function registerFiles(
        array $avroFiles,
        SymfonyStyle $io,
        array &$failed = [],
        array &$succeeded = [],
        bool $useSchemaVersioning = false
    ): bool {
        foreach ($avroFiles as $schemaName => $avroFile) {
            /** @var string $fileContents */
            $fileContents = file_get_contents($avroFile);

            /** @var array<string, mixed> $jsonDecoded */
            $jsonDecoded = json_decode($fileContents);

            /** @var non-empty-string $localSchema */
            $localSchema = json_encode($jsonDecoded);

            if ($useSchemaVersioning) {
                /** @var string $schemaName */
                $schemaName = preg_replace('/[.0-9]*$/', '', $schemaName);
            }

            try {
                $latestVersion = $this->schemaRegistryApi->getLatestSubjectVersion($schemaName);
            } catch (\Throwable) {
                $latestVersion = null;
            }

            if (null !== $latestVersion) {
                if (true === $this->schemaRegistryApi->isSchemaAlreadyRegistered($schemaName, $localSchema)) {
                    $io->writeln(sprintf('Schema %s has been skipped (no change)', $schemaName));
                    continue;
                }

                if (false === $this->schemaRegistryApi->checkSchemaCompatibilityForVersion($schemaName, $localSchema)) {
                    $io->error(sprintf('Schema %s has an incompatible change', $schemaName));
                    return false;
                }
            }

            try {
                AvroSchema::parse($localSchema);
            } catch (AvroSchemaParseException $e) {
                $io->writeln(sprintf('Skipping %s for now because %s', $schemaName, $e->getMessage()));
                $failed[$schemaName] = $schemaName;
                continue;
            }

            $this->schemaRegistryApi->registerNewSchemaVersion($schemaName, $localSchema);

            $succeeded[$schemaName] = [
                'name' => $schemaName,
                'version' => $this->schemaRegistryApi->getVersionForSchema($schemaName, $localSchema),
            ];
            unset($failed[$schemaName]);

            $io->writeln(sprintf('Successfully registered new version of schema %s', $schemaName));
        }

        return true;
    }
}
