<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetCompatibilityModeForSchemaCommand extends AbstractSchemaCommand
{
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:set:schema:compatibility:mode')
            ->setDescription('Set the compatibility mode for a given schema')
            ->setHelp('Set the compatibility mode for a given schema')
            ->addArgument('schemaName', InputArgument::REQUIRED, 'Name of the schema')
            ->addArgument('compatibilityLevel', InputArgument::REQUIRED, 'Compatibility level to set');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $schemaName = (string) $input->getArgument('schemaName');
        $compatibilityLevel = (string) $input->getArgument('compatibilityLevel');

        try {
            $this->schemaRegistryApi->setSubjectCompatibilityLevel($schemaName, $compatibilityLevel);
        } catch (\Exception $exception) {
            $output->writeln(
                sprintf('Could not change compatibility mode for schema %s: %s', $schemaName, $exception->getMessage())
            );

            return Command::FAILURE;
        }

        $output->writeln(sprintf('Successfully changed compatibility mode for schema: %s', $schemaName));

        return Command::SUCCESS;
    }
}
