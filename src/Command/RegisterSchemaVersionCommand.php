<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use FlixTech\SchemaRegistryApi\SynchronousRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterSchemaVersionCommand extends Command
{

    /**
     * @var SynchronousRegistry
     */
    private $registry;

    /**
     * @param SynchronousRegistry $registry
     */
    public function __construct(SynchronousRegistry $registry)
    {
        parent::__construct();
        $this->registry = $registry;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schema:registry:register:version')
            ->setDescription('Add new schema version to registry')
            ->setHelp('Add new schema version to registry')
            ->addArgument('registryUrl', InputArgument::REQUIRED, 'Url of the schema registry')
            ->addArgument('schemaFile', InputArgument::REQUIRED, 'Path to avro schema file')
            ->addArgument('schemaName', InputArgument::REQUIRED, 'Name of the schema');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Add new schema version to registry');

        $schemaPath = realpath($input->getArgument('schemaFile'));
        $schemaName = $input->getArgument('schemaName');

        $avroSchema = \AvroSchema::parse(file_get_contents($schemaPath));

        $result = $this->registry->register($schemaName, $avroSchema);

        $output->writeln(sprintf('Successfully registered new schema with id: %d', $result['id']));
    }
}
