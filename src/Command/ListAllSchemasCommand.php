<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputArgument;
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
            ->setHelp('List all schemas')
            ->addArgument(
                'includeDeleted',
                InputArgument::OPTIONAL,
                'Include deleted schemas (use 
                \'true\' or \'false\')'
            );
    }

    #[\Override]
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $includeDeletedArg = $input->getArgument('includeDeleted');

        if (null !== $includeDeletedArg && false === in_array($includeDeletedArg, ['true', 'false'], true)) {
            $output->writeln('Invalid \'includeDeleted\' argument. Allowed values are \'true\' or \'false\'.');

            return 1;
        }

        $schemas = $this->schemaRegistryApi->getSubjects('true' === $includeDeletedArg);

        foreach ($schemas as $schema) {
            $output->writeln((string) $schema);
        }

        return 0;
    }
}
