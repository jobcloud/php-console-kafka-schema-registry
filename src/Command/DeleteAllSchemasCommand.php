<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function FlixTech\SchemaRegistryApi\Requests\allSubjectsRequest;
use function FlixTech\SchemaRegistryApi\Requests\deleteSubjectRequest;

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
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $response = $this->client->send(allSubjectsRequest());
        $schemas = $this->getJsonDataFromResponse($response);

        foreach ($schemas as $schema) {
            $this->client->send(deleteSubjectRequest($schema));
        }

        $output->writeln(sprintf('All schemas deleted.'));

        return 0;
    }
}
