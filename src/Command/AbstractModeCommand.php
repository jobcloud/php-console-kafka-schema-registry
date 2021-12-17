<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractModeCommand extends AbstractSchemaCommand implements ModeCommandInterface
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName(sprintf('kafka-schema-registry:set:mode:%s', strtolower($this->getMode())))
            ->setDescription(sprintf("Sets import mode to %s", $this->getMode()))
            ->setHelp(sprintf("Sets import mode to %s", $this->getMode()));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (true === $this->schemaRegistryApi->setImportMode($this->getMode())) {
            $output->writeln(sprintf('Import mode set to %s', $this->getMode()));
            return 0;
        }

        return 1;
    }
}
