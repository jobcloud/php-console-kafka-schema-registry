<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractImportCommand extends AbstractSchemaCommand implements ImportCommandInterface
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     * @throws ClientException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $outcome = $this->schemaRegistryApi->setImportMode($this->getMode());

        if (true === $outcome) {
            $output->writeln(
                sprintf('Import mode set to %s', $this->getMode())
            );
        }

        return (int) !$outcome;
    }
}
