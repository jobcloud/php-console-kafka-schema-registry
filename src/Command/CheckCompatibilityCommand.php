<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function FlixTech\SchemaRegistryApi\Requests\checkSchemaCompatibilityAgainstVersionRequest;

class CheckCompatibilityCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schema:registry:compatibility')
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
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->client->send(
            checkSchemaCompatibilityAgainstVersionRequest(
                SchemaFileHelper::readSchemaFromFile($input->getArgument('schemaFile')),
                SchemaFileHelper::readSchemaName($input->getArgument('schemaFile')),
                $input->getArgument('schemaVersion')
            )
        );

        $data = json_decode($response->getBody()->getContents(), true, 2, JSON_THROW_ON_ERROR);

        $output->writeln(
            sprintf('Schema is %s', (bool) $data['is_compatible'] ? 'Compatible' : 'NOT Compatible')
        );

        return (int) $data['is_compatible'];
    }
}
