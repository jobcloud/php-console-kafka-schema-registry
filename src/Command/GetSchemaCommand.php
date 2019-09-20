<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function FlixTech\SchemaRegistryApi\Requests\checkIfSubjectHasSchemaRegisteredRequest;
use function FlixTech\SchemaRegistryApi\Requests\schemaRequest;

class GetSchemaCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schema:registry:get:schema')
            ->setDescription('Get schema')
            ->setHelp('Get schema')
            ->addArgument('schemaName', InputArgument::REQUIRED, 'Name of the schema')
            ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        // TODO check best way to get ID
        $id = $this->getIdBySchemaName($input->getArgument('schemaName'));

        try {
            $response = $this->client->send(schemaRequest($id));
        }catch (ClientException $e)
        {
            if( $e->getCode() !== 404){
                throw $e;
            }

            $output->writeln('Schema does not exist by given ID');
            return 1;
        }

        $data = $this->getJsonDataFromResponse($response);
        $output->writeln($data['schema']);
        return 0;
    }

    /**
     * @param string $schemaName
     * @return string|null
     */
    private function getIdBySchemaName(string $schemaName): ?string
    {
        try {
            $response = $this->client->send(
                checkIfSubjectHasSchemaRegisteredRequest(
                    $schemaName
                )
            );

            return $this->getJsonDataFromResponse($response);
        }catch (ClientException $e) {
            if ($e->getCode() !== 40403) {
                throw $e;
            }

            return null;
        }
    }
}
