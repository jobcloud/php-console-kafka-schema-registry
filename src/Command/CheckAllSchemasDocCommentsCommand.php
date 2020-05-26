<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\RequestException;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;

/**
 * Class CheckAllSchemasDocCommentsCommand
 */
class CheckAllSchemasDocCommentsCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:check:doc:comments:all')
            ->setDescription('Checks for doc comments for all schemas in folder')
            ->setHelp('Checks for doc comments for all schemas in folder')
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

        if (false === $this->checkDocCommentsOnSchemas($avroFiles, $failed)) {
            $io->error('Following schemas do not have doc comments on all fields:');
            $io->listing($failed);

            return 1;
        }

        $io->success('All schemas have doc comments on all fields');

        return 0;
    }


    /**
     * @param array $avroFiles
     * @param array $failed
     * @return boolean
     */
    private function checkDocCommentsOnSchemas(array $avroFiles, array &$failed = []): bool
    {
        $failed = [];

        foreach ($avroFiles as $schemaName => $avroFile) {

            /** @var string|false $localSchema */
            $localSchema = file_get_contents($avroFile);

            if (false === $localSchema) {
                $failed[] = $schemaName;

                continue;
            }

            $localSchema = trim($localSchema);

            $schema = json_decode($localSchema, true);

            $decodeError = json_last_error();

            if ($decodeError !== JSON_ERROR_NONE || false === SchemaFileHelper::hasDocCommentsOnAllFields($schema)) {
                $failed[] = $schemaName;
            }
        }

        return 0 === count($failed);
    }
}
