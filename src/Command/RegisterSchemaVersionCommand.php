<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use AvroSchemaParseException;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterSchemaVersionCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schema:registry:register:version')
            ->setDescription('Add new schema version to registry')
            ->setHelp('Add new schema version to registry')
            ->addArgument('schemaFile', InputArgument::REQUIRED, 'Path to avro schema file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws AvroSchemaParseException
     * @throws SchemaRegistryException
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Add new schema version to registry');

        $avroSchema = SchemaFileHelper::readAvroSchemaFromFile($input->getArgument('schemaFile'));
        $schemaName = SchemaFileHelper::readSchemaName($input->getArgument('schemaFile'));

        $result = $this->registry->register($schemaName, $avroSchema);

        $output->writeln(sprintf('Successfully registered new schema with id: %d', $result['id']));
    }
}
