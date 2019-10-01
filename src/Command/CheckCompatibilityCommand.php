<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\GuzzleException;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
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
            ->setName('schema:registry:check:compatibility')
            ->setDescription('Check Schema Compatibility against version')
            ->setHelp('Check Schema Compatibility against version')
            ->addArgument('schemaFile', InputArgument::REQUIRED, 'Path to avro schema file')
            ->addArgument('schemaVersion', InputArgument::REQUIRED, 'Version of the schema')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws GuzzleException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = $this->schemaRegistryApi->checkSchemaCompatibilityAgainstVersionRequest(
            SchemaFileHelper::readSchemaFromFile($input->getArgument('schemaFile')),
            SchemaFileHelper::getSchemaName($input->getArgument('schemaFile')),
            $input->getArgument('schemaVersion')
        );

        $output->writeln(
            sprintf('Schema is %s', $data['is_compatible'] ? 'Compatible' : 'NOT Compatible')
        );

        return (int) $data['is_compatible'];
    }
}
