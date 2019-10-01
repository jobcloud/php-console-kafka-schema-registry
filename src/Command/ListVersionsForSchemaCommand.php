<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListVersionsForSchemaCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:list:versions')
            ->setDescription('List all versions for given schema')
            ->setHelp('List all versions for given schema')
            ->addArgument('schemaName', InputArgument::REQUIRED, 'Name of the schema');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        /** @var string $schemaName */
        $schemaName = $input->getArgument('schemaName');

        $schemaVersions = $this->schemaRegistryApi->getAllSchemaVersions($schemaName);

        foreach ($schemaVersions as $schemaVersion) {
            $output->writeln($schemaVersion);
        }

        return 0;
    }
}
