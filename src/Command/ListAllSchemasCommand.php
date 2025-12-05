<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListAllSchemasCommand extends AbstractSchemaCommand
{
    #[\Override]
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:list')
            ->setDescription('List all schemas')
            ->setHelp('List all schemas');
    }

    #[\Override]
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $schemas = $this->schemaRegistryApi->getSubjects();

        foreach ($schemas as $schema) {
            $output->writeln((string) $schema);
        }

        return 0;
    }
}
