<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use AvroSchemaParseException;
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
            ->setName('kafka-schema-registry:register:version')
            ->setDescription('Add new schema version to registry')
            ->setHelp('Add new schema version to registry')
            ->addArgument('schemaFile', InputArgument::REQUIRED, 'Path to Avro schema file');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     * @throws AvroSchemaParseException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string $schemaFile */
        $schemaFile = $input->getArgument('schemaFile');

        $output->writeln('Add new schema version to registry');

        $avroSchema = SchemaFileHelper::readAvroSchemaFromFile($schemaFile);
        $schemaName = SchemaFileHelper::getSchemaName($schemaFile);

        $result = $this->schemaRegistryApi->registerNewSchemaVersion($schemaName, (string) $avroSchema);

        $output->writeln(sprintf('Successfully registered new schema with id: %d', $result['id']));

        return 0;
    }
}
