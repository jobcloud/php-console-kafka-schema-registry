<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaRegistryExceptionInterface;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class GetSchemaByVersionCommand extends AbstractSchemaCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:fetch:schema')
            ->setDescription('Get schema by version number')
            ->setHelp('Get schema by version number')
            ->addArgument('schemaName', InputArgument::REQUIRED, 'Name of the schema')
            ->addArgument('schemaVersion', InputArgument::REQUIRED, 'Version of the schema')
            ->addArgument('outputFile', InputArgument::REQUIRED, 'Path to output file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     * @throws ClientExceptionInterface
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $outputFile */
        $outputFile = $input->getArgument('outputFile');

        /** @var string $schemaName */
        $schemaName = $input->getArgument('schemaName');

        /** @var string $schemaVersion */
        $schemaVersion = $input->getArgument('schemaVersion');

        $schema = $this->schemaRegistryApi->getSchemaDefinitionByVersion($schemaName, $schemaVersion);

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
