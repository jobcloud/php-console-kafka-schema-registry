<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\GuzzleException;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckIsRegistredCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:entry:exists')
            ->setDescription('Check if schema already exists')
            ->setHelp('Check if schema already exists')
            ->addArgument('schemaFile', InputArgument::REQUIRED, 'Path to Avro schema file')
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

            $version = $this->schemaRegistryApi->getVersionForSchema(
                SchemaFileHelper::getSchemaName($input->getArgument('schemaFile')),
                SchemaFileHelper::readSchemaFromFile($input->getArgument('schemaFile'))
            );

            if(null === $version) {
                $output->writeln('Schema does not exist in any version');
                return -1;
            }

            $output->writeln(sprintf('Schema exists in version %d' , $version));
            return 0;
    }
}
