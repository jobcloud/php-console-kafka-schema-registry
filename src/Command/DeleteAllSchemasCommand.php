<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use mysql_xdevapi\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->setHelp('Delete all schemas')
            ->addOption(
                'hard',
                null,
                InputOption::VALUE_NONE,
                'Hard delete of a schema (removes all metadata, including schema ID)'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $schemas = $this->schemaRegistryApi->getSubjects();

        $hardDelete = (bool) $input->getOption('hard');

        foreach ($schemas as $schemaName) {
            try {
                $this->schemaRegistryApi->deleteSubject($schemaName);
            } catch (Exception $e) {
                var_dump($e);
            }

            if ($hardDelete) {
                try {
                    $this->schemaRegistryApi->deleteSubject(
                        sprintf('%s%s', $schemaName, '?permanent=true')
                    );
                } catch (Exception $e) {
                    var_dump($e);
                }
            }
        }

        $output->writeln('All schemas deleted.');

        return 0;
    }
}
