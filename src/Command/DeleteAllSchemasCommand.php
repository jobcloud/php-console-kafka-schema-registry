<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\GuzzleException;
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
            ->setName('schema:registry:delete:all')
            ->setDescription('Delete all schemas')
            ->setHelp('Delete all schemas');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws GuzzleException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $schemas = $this->schemaRegistryApi->allSubjectsRequest();

        foreach ($schemas as $schemaName) {
            $this->schemaRegistryApi->deleteSubjectRequest($schemaName);
        }

        $output->writeln(sprintf('All schemas deleted.'));

        return 0;
    }
}
