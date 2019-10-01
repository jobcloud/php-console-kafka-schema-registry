<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetSchemaByVersionCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schema:registry:fetch:schema')
            ->setDescription('List all versions for given schema')
            ->setHelp('List all versions for given schema')
            ->addArgument('schemaName', InputArgument::REQUIRED, 'Name of the schema')
            ->addArgument('schemaVersion', InputArgument::REQUIRED, 'Version of the schema')
            ->addArgument('outputFile', InputArgument::REQUIRED, 'Path to output file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws GuzzleException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $outputFile = $input->getArgument('outputFile');
        $data = $this->schemaRegistryApi->singleSubjectVersionRequest(
            $input->getArgument('schemaName'),
            $input->getArgument('schemaVersion'),
        );

        if (false === file_put_contents($outputFile, $data['schema'])) {
            $output->writeln(sprintf('Was unable to write schema to %s.', $outputFile));
            return -1;
        }

        $output->writeln(sprintf('Schema successfully written to %s.', $outputFile));
        return 0;
    }
}
