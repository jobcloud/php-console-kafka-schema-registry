<?php

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\SchemaConsole\Helper\Avro;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;
use \FilesystemIterator;
use \SplFileInfo;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function FlixTech\SchemaRegistryApi\Requests\checkSchemaCompatibilityAgainstVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\singleSubjectVersionRequest;
use const FlixTech\SchemaRegistryApi\Constants\VERSION_LATEST;


class RegisterChangedSchemasCommand extends AbstractSchemaCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schema:registry:register:changed')
            ->setDescription('Register all changed schemas from a path')
            ->setHelp('Register all changed schemas from a path')
            ->addArgument('schemaDirectory', InputArgument::REQUIRED, 'Path to avro schema directory')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $directory = $input->getArgument('schemaDirectory');

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS
            )
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (Avro::FILE_EXTENSION !== $file->getExtension()) {
                continue;
            }

            $localSchema = json_encode(json_decode(file_get_contents($file->getFilename())));
            $latestSchema = $response = $this->client->send(
                singleSubjectVersionRequest($file->getBasename(), VERSION_LATEST)
            );

            if ($latestSchema === $localSchema) {
                continue;
            }

            $response = $this->client->send(
                checkSchemaCompatibilityAgainstVersionRequest(
                    $localSchema,
                    $file->getBasename(),
                    VERSION_LATEST
                )
            );

            if (false === $response['is_compatible']) {
                $output->writeln(sprintf('Schema %s has an incompatible change', $file->getBasename()));
                return -1;
            }

            $result = $this->registry->register($file->getBasename(), $localSchema);

            $output->writeln(sprintf('Successfully registered new version of schema %s', $file->getBasename()));
        }

        return 0;
    }
}
