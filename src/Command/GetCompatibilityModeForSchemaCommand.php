<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetCompatibilityModeForSchemaCommand extends AbstractSchemaCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:get:schema:compatibility:mode')
            ->setDescription('Get the compatibility mode for a given schema')
            ->setHelp('Get the compatibility mode for a given schema')
            ->addArgument('schemaName', InputArgument::REQUIRED, 'Name of the schema');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $schemaName */
        $schemaName = $input->getArgument('schemaName');

        $compatibilityLevel = $this->schemaRegistryApi->getSubjectCompatibilityLevel($schemaName);

        $output->writeln(
            sprintf('The schema\'s compatibility mode is %s', $compatibilityLevel)
        );

        return 0;
    }
}
