<?php

namespace Jobcloud\SchemaConsole\Command;

use AvroSchema;
use AvroSchemaParseException;
use GuzzleHttp\Exception\RequestException;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckAllSchemasHaveValidStructureCommand extends Command
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:check:structure:all')
            ->setDescription('Checks structure for all schemas in folder')
            ->setHelp('Checks structure for all schemas in folder')
            ->addArgument('schemaDirectory', InputArgument::REQUIRED, 'Path to avro schema directory');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     * @throws RequestException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $directory */
        $directory = $input->getArgument('schemaDirectory');
        $avroFiles = SchemaFileHelper::getAvroFiles($directory);

        $io = new SymfonyStyle($input, $output);

        $failed = [];

        if (false === $this->checkSchemas($avroFiles, $failed)) {
            $io->error('Following schemas have invalid structure:');
            $io->listing($failed);

            return 1;
        }

        $io->success('All schemas are well structured');

        return 0;
    }


    /**
     * @param array $avroFiles
     * @param array $failed
     * @return boolean
     */
    private function checkSchemas(array $avroFiles, array &$failed = []): bool
    {
        $failed = [];

        foreach ($avroFiles as $schemaName => $avroFile) {

            /** @var string $localSchema */
            $localSchema = file_get_contents($avroFile);

            try {
                AvroSchema::parse($localSchema);
            } catch (AvroSchemaParseException $e) {
                $failed[] = $schemaName;
                continue;
            }
        }

        return 0 === count($failed);
    }
}
