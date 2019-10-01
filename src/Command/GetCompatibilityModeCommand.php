<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\GuzzleException;
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
            ->setName('schema:registry:get:compatibility:mode')
            ->setDescription('Get the default compatibility mode of the registry')
            ->setHelp('Get the default compatibility mode of the registry');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer
     * @throws GuzzleException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = $this->schemaRegistryApi->defaultCompatibilityLevelRequest();

        $output->writeln(
            sprintf('The registry\'s default compatibility mode is %s', $data['compatibilityLevel'])
        );

        return 0;
    }
}
