<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaRegistryExceptionInterface;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCompatibilityCommand extends AbstractSchemaCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:check:compatibility')
            ->setDescription('Check Schema Compatibility against version')
            ->setHelp('Check Schema Compatibility against version')
            ->addArgument('schemaFile', InputArgument::REQUIRED, 'Path to Avro schema file')
            ->addArgument('schemaVersion', InputArgument::REQUIRED, 'Version of the schema');
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
        /** @var string $schemaFile */
        $schemaFile = $input->getArgument('schemaFile');

        /** @var string $schemaVersion */
        $schemaVersion = $input->getArgument('schemaVersion');

        $compatible = $this->schemaRegistryApi->checkSchemaCompatibilityForVersion(
            SchemaFileHelper::getSchemaName($schemaFile),
            SchemaFileHelper::readSchemaFromFile($schemaFile),
            (string) $schemaVersion
        );

        $output->writeln(
            sprintf('Schema is %s', $compatible ? 'Compatible' : 'NOT Compatible')
        );

        // Program exits 1 (fail) when FALSE, 0 if TRUE (success)
        return (int) !$compatible;
    }
}
