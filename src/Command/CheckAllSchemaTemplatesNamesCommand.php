<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckAllSchemaTemplatesNamesCommand extends Command
{
    private const TYPES_FOR_VALIDATION = [
        'record',
        'enum',
        'fixed'
    ];

    private const REGEX_MATCH_NAME_NAMING_CONVENTION = '/^[A-Za-z_][A-Za-z0-9_]*$/';

    private const REGEX_MATCH_NAMESPACE_NAMING_CONVENTION =
        '/^(?:[A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)*)?$/';

    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:check:template:names:all')
            ->setDescription('Checks if template names follow avro naming convention')
            ->setHelp('Checks if template names follow avro naming convention')
            ->addArgument(
                'schemaTemplateDirectory',
                InputArgument::REQUIRED,
                'Path to avro schema template directory'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $directory */
        $directory = $input->getArgument('schemaTemplateDirectory');
        $avroFiles = SchemaFileHelper::getAvroFiles($directory);

        $io = new SymfonyStyle($input, $output);

        $failed = [];

        if (false === $this->checkSchemaTemplateNames($avroFiles, $failed)) {
            $io->error('A template schema names must comply with the following AVRO naming conventions:
https://avro.apache.org/docs/current/spec.html#names
The following template schema names violate the aforementioned rules:');
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
    private function checkSchemaTemplateNames(array $avroFiles, array &$failed = []): bool
    {
        $failed = [];

        foreach ($avroFiles as $schemaName => $avroFile) {
            /** @var string $localSchema */
            $localSchema = file_get_contents($avroFile);

            $decodedSchema = json_decode($localSchema);

            if (
                property_exists($decodedSchema, 'type')
                && in_array($decodedSchema->type, self::TYPES_FOR_VALIDATION)
            ) {
                $failed = array_merge($failed, $this->validateNameField($decodedSchema->name, $schemaName));

                if (property_exists($decodedSchema, 'namespace')) {
                    $namespace = $decodedSchema->namespace;
                    $failed = array_merge($failed, $this->validateNamespaceField($namespace, $schemaName));
                }
            }
        }

        return 0 === count($failed);
    }

    /**
     * @return array<int, string>
     */
    private function validateNamespaceField(string $namespace, string $schemaName): array
    {
        $failed = [];

        if ('' === $namespace) {
            return $failed;
        }

        if (!preg_match(self::REGEX_MATCH_NAMESPACE_NAMING_CONVENTION, $namespace)) {
            $failed[] = $schemaName;
        }

        return $failed;
    }

    /**
     * @return array<int, string>
     */
    private function validateNameField(string $name, string $schemaName): array
    {
        $failed = [];

        if (!preg_match(self::REGEX_MATCH_NAME_NAMING_CONVENTION, $name)) {
            $failed[] = $schemaName;
        }

        return $failed;
    }
}
