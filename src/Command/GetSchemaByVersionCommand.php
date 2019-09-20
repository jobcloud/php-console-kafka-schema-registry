<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function FlixTech\SchemaRegistryApi\Requests\singleSubjectVersionRequest;

class GetSchemaByVersionCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schema:registry:get:schema:version')
            ->setDescription('List all versions for given schema')
            ->setHelp('List all versions for given schema')
            ->addArgument('schemaName', InputArgument::REQUIRED, 'Name of the schema')
            ->addArgument('schemaVersion', InputArgument::REQUIRED, 'Version of the schema');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $response = $this->client->send(
            singleSubjectVersionRequest(
                $input->getArgument('schemaName'),
                $input->getArgument('schemaVersion'),
            )
        );

        $data = $this->getJsonDataFromResponse($response);

        $output->writeln($data['schema']);
        return 0;
    }
}
