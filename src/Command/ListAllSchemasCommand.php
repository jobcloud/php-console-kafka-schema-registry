<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaRegistryExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JsonException;

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
            ->setHelp('List all schemas');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     * @throws ClientExceptionInterface
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $schemas = $this->schemaRegistryApi->getSubjects();

        foreach ($schemas as $schema) {
            $output->writeln($schema);
        }

        return 0;
    }
}
