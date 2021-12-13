<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\ClientException;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class GetLatestSchemaCommand extends AbstractSchemaCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:get:schema:latest')
            ->setDescription('Get latest schema')
            ->setHelp('Get latest schema')
            ->addArgument('schemaName', InputArgument::REQUIRED, 'Name of the schema')
            ->addArgument('outputFile', InputArgument::REQUIRED, 'Path to output file');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     * @throws ClientException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $schemaName */
        $schemaName = $input->getArgument('schemaName');

        try {
            $schema = $this->schemaRegistryApi->getSchemaDefinitionByVersion(
                $schemaName,
                KafkaSchemaRegistryApiClientInterface::VERSION_LATEST
            );
        } catch (ClientException $e) {
            if ($e->getCode() !== 404) {
                throw $e;
            }

            $output->writeln(sprintf('Schema %s does not exist', $schemaName));
            return 1;
        }

        /** @var string $outputFile */
        $outputFile = $input->getArgument('outputFile');

        try {
            file_put_contents($outputFile, json_encode($schema, JSON_THROW_ON_ERROR));
        } catch (Throwable $e) {
            $output->writeln(sprintf('Was unable to write schema to %s.', $outputFile));
            return 1;
        }

        $output->writeln(sprintf('Schema successfully written to %s.', $outputFile));

        return 0;
    }
}
