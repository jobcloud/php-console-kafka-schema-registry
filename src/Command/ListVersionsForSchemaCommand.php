<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function FlixTech\SchemaRegistryApi\Requests\allSubjectVersionsRequest;

class ListVersionsForSchemaCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schema:registry:list:versions')
            ->setDescription('List all versions for given schema')
            ->setHelp('List all versions for given schema')
            ->addArgument('schemaName', InputArgument::REQUIRED, 'Name of the schema');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $response = $this->client->send(
            allSubjectVersionsRequest(
                $input->getArgument('schemaName')
            )
        );

        $schemaVersions = $this->getJsonDataFromResponse($response);

        foreach($schemaVersions as $schemaVersion) {
            $output->writeln($schemaVersion);
        };

        return 0;
    }
}
