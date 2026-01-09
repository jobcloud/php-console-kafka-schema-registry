<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListAllSchemasCommand extends AbstractSchemaCommand
{
    /**
     * @return void
     */
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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $includeDeletedArg = $input->getArgument('includeDeleted');
        $output->writeln('arg: ' . $includeDeletedArg);

        if ($includeDeletedArg !== null && $includeDeletedArg !== 'true' && $includeDeletedArg !== 'false') {
            $output->writeln('if');
            // phpcs:ignore
            $message = '<error>Invalid value for \'deletedSchemas\' argument. Allowed values are \'true\' or \'false\'.</error>';

            $output->writeln($message);

            return 1;
        }

        $output->writeln('value:' . ($includeDeletedArg === 'true' ? 'true' : 'false'));
        $schemas = $this->schemaRegistryApi->getSubjects($includeDeletedArg === 'true');

        foreach ($schemas as $schema) {
            $output->writeln((string) $schema);
        }

        return 0;
    }
}
