<?php

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckAllSchemaTemplatesNameFieldsCommand extends Command
{
    private const TYPES_FOR_VALIDATION = [
        'record',
        'enum',
        'fixed'
    ];

    private const REGEX_MATCH_FIRST_LETTER = '/^[A-Za-z_]/';

    private const REGEX_MATCH_STRING = '/^[A-Za-z0-9_]+$/';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:check:template:name:field:all')
            ->setDescription('Checks if name field follows avro naming convention')
            ->setHelp('Checks if name field follows avro naming convention')
            ->addArgument(
                'schemaTemplateDirectory',
                InputArgument::REQUIRED,
                'Path to avro schema template directory'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $directory */
        $directory = $input->getArgument('schemaTemplateDirectory');
        $avroFiles = SchemaFileHelper::getAvroFiles($directory);

        $io = new SymfonyStyle($input, $output);

        $failed = [];

        if (false === $this->checkSchemaTemplateNameFields($avroFiles, $failed)) {
            $io->error('Following schema templates have invalid name field:');
            $io->listing($failed);

            return 1;
        }

        $io->success('All schema templates have valid name fields');

        return 0;
    }

    /**
     * @param array<string, mixed> $avroFiles
     * @param array<string, mixed> $failed
     * @return boolean
     */
    private function checkSchemaTemplateNameFields(array $avroFiles, array &$failed = []): bool
    {
        $failed = [];

        foreach ($avroFiles as $schemaName => $avroFile) {
            /** @var string $localSchema */
            $localSchema = file_get_contents($avroFile);

            $decodedSchema = json_decode($localSchema);
            if (
                property_exists($decodedSchema, 'type')
                && property_exists($decodedSchema, 'name')
                && in_array($decodedSchema->type, self::TYPES_FOR_VALIDATION)
            ) {
                if (
                    preg_match(self::REGEX_MATCH_FIRST_LETTER, $decodedSchema->name)
                    && preg_match(self::REGEX_MATCH_STRING, $decodedSchema->name)
                ) {
                    continue;
                } else {
                    $failed[] = $schemaName;
                }
            }
        }

        return 0 === count($failed);
    }
}
