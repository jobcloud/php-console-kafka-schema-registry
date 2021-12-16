<?php

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\SchemaRegistryExceptionInterface;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckAllSchemasCompatibilityCommand extends AbstractSchemaCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:check:compatibility:all')
            ->setDescription('Checks for compatibility for all schemas in folder')
            ->setHelp('Checks for compatibility for all schemas in folder')
            ->addArgument('schemaDirectory', InputArgument::REQUIRED, 'Path to avro schema directory');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer
     * @throws ClientExceptionInterface
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $directory */
        $directory = $input->getArgument('schemaDirectory');
        $avroFiles = SchemaFileHelper::getAvroFiles($directory);

        $io = new SymfonyStyle($input, $output);

        $failed = [];

        if (false === $this->checkSchemas($avroFiles, $failed)) {
            $io->error('Following schemas are not compatible:');
            $io->listing($failed);

            return 1;
        }

        $io->success('All schemas are compatible');

        return 0;
    }


    /**
     * @param array<string, mixed> $avroFiles
     * @param array<string, mixed> $failed
     * @return boolean
     * @throws SchemaRegistryExceptionInterface
     * @throws JsonException
     * @throws ClientExceptionInterface
     */
    private function checkSchemas(array $avroFiles, array &$failed = []): bool
    {
        $failed = [];

        foreach ($avroFiles as $schemaName => $avroFile) {

            /** @var string $localSchema */
            $localSchema = file_get_contents($avroFile);

            if (false === $this->schemaRegistryApi->checkSchemaCompatibilityForVersion($schemaName, $localSchema)) {
                $failed[] = $schemaName;
            }
        }

        return 0 === count($failed);
    }
}
