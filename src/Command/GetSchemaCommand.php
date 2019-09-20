<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
            ->addArgument('schemaId', InputArgument::REQUIRED, 'Id of the schema')
            ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $response = $this->client->send(schemaRequest($input->getArgument('schemaId')));
        }catch (ClientException $e)
        {
            if( $e->getCode() !== 404){
                throw $e;
            }

            $output->writeln('Schema does not exist by given ID');
            return 1;
        }

        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $output->writeln($data['schema']);
        return 0;
    }
}
