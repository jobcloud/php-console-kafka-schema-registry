<?php

namespace Jobcloud\SchemaConsole\Command;

use FilesystemIterator;
use GuzzleHttp\Exception\RequestException;
use Jobcloud\SchemaConsole\Helper\Avro;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class CheckAllSchemasCompatibilityCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:check:compatibility:all')
            ->setDescription('Checks for compatibility for all schemas in folder')
            ->setHelp('Checks for compatibility for all schemas in folder')
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

        $io = new SymfonyStyle($input, $output);

        $failed = [];

        if (false === $this->checkSchemas($avroFiles, $failed)) {
            $io->error('Following schemas are not compatible:');
            $io->listing($failed);

            return 1;
        }

        $io->success('All schemas are compatible');

        return 0;
    }

    /**
     * @param string $directory
     * @return array
     */
    protected function getAvroFiles(string $directory): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS
            )
        );

        $files = [];

        /** @var SplFileInfo $file */
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
     * @param array $avroFiles
     * @param array $failed
     * @return boolean
     */
    private function checkSchemas(array $avroFiles, array &$failed = []): bool
    {
        $failed = [];

        foreach ($avroFiles as $schemaName => $avroFile) {
            /** @var string $fileContents */
            $fileContents = file_get_contents($avroFile);

            /** @var array $jsonDecoded */
            $jsonDecoded = json_decode($fileContents);

            /** @var string $localSchema */
            $localSchema = json_encode($jsonDecoded);

            /** @var string $latestVersion */
            $latestVersion = $this->schemaRegistryApi->getLatestSchemaVersion($schemaName);

            if (false === $this->isLocalSchemaCompatible($schemaName, $localSchema, $latestVersion)) {
                $failed[] = $schemaName;
            }
        }

        return 0 === count($failed);
    }
}
