<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetCompatibilityModeCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:get:compatibility:mode')
            ->setDescription('Get the default compatibility mode of the registry')
            ->setHelp('Get the default compatibility mode of the registry');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $compatibilityLevel = $this->schemaRegistryApi->getDefaultCompatibilityLevel();

        $output->writeln(
            sprintf('The registry\'s default compatibility mode is %s', $compatibilityLevel)
        );

        return 0;
    }
}
