<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function FlixTech\SchemaRegistryApi\Requests\allSubjectsRequest;

class ListAllSchemasCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schema:registry:list')
            ->setDescription('List all schemas')
            ->setHelp('List all schemas');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $response = $this->client->send(allSubjectsRequest());
        $data = json_decode($response->getBody()->getContents(), true, 2, JSON_THROW_ON_ERROR);

        array_walk($data, static function($item) use ($output){
            $output->writeln($item);
        });

        return 0;
    }
}
