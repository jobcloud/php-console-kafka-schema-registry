<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteAllSchemasCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:delete:all')
            ->setDescription('Delete all schemas')
            ->setHelp('Delete all schemas');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $schemas = $this->schemaRegistryApi->getSubjects();

        foreach ($schemas as $schemaName) {
            $this->schemaRegistryApi->deleteSubject($schemaName);
        }

        $output->writeln('All schemas deleted.');

        return 0;
    }
}
