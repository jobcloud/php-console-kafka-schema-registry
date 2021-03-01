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

class CheckAllSchemasAreValidAvroCommand extends Command
{
    private const TYPE_MAP = [
        "null" => "null",
        "boolean" => "boolean",
        "integer" => "int",
        "string" => "string",
        "double" => "double",
        "array" => "array",
    ];

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:check:valid:avro:all')
            ->setDescription('Checks that all schemas are valid Avro')
            ->setHelp('Checks that all schemas are valid Avro')
            ->addArgument('schemaDirectory', InputArgument::REQUIRED, 'Path to Avro schema directory');
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
            $io->error('Following schemas are not valid Avro:');
            $io->listing($failed);

            return 1;
        }

        $io->success('All schemas are valid Avro');

        return 0;
    }


    /**
     * @param array<string, mixed> $avroFiles
     * @param array<string, mixed> $failed
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

                $this->checkDefaultType($localSchema);
            } catch (AvroSchemaParseException $e) {
                $failed[] = $schemaName;
                continue;
            }
        }

        return 0 === count($failed);
    }

    /**
     * @param string $localSchema
     * @throws AvroSchemaParseException
     */
    private function checkDefaultType(string $localSchema): void
    {
        $decodedSchema = json_decode($localSchema);
        if (!property_exists($decodedSchema, 'fields')) {
            return;
        }

        $result = [
            'found' => 0,
            'default' => 0,
        ];

        $result = $this->checkAllFields($decodedSchema->fields, $result);

        if ($result['found'] !== $result['default']) {
            throw new AvroSchemaParseException('Type of default value is not in field type.');
        }
    }

    /**
     * @param array<string, mixed> $schemaFields
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     * @throws AvroSchemaParseException
     */
    private function checkAllFields(array $schemaFields, array $result): array
    {
        foreach ($schemaFields as $field) {
            $fieldTypes = $field->type;
            if (property_exists($field, 'default')) {
                $result['default']++;
            }

            if (is_array($fieldTypes)) {
                foreach ($fieldTypes as $fieldType) {
                    $result = $this->checkSingleField($fieldType, $field, $result);
                }
            }

            if (!is_array($fieldTypes)) {
                $result = $this->checkSingleField($fieldTypes, $field, $result);
            }
        }

        return $result;
    }

    /**
     * @param mixed $fieldType
     * @param mixed $field
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     * @throws AvroSchemaParseException
     */
    private function checkSingleField($fieldType, $field, array $result): array
    {
        $defaultType = null;

        if (property_exists($field, 'default')) {
            $defaultType = strtolower(gettype($field->default));

            if (is_string($fieldType)) {
                if (
                    self::TYPE_MAP[$defaultType] === $fieldType
                    || $this->isContainedInBiggerType(self::TYPE_MAP[$defaultType], $fieldType)
                ) {
                    $result['found']++;
                }
            }
        }

        if (property_exists($fieldType, 'type')) {
            if ($fieldType->type === 'record') {
                $result = $this->checkAllFields($fieldType->fields, $result);
            }

            if ($fieldType->type === 'array') {
                if (is_string($defaultType) && self::TYPE_MAP[$defaultType] === $fieldType->type) {
                    $result['found']++;
                }
                if (!is_array($fieldType->items) && property_exists($fieldType->items, "type")) {
                    $result = $this->checkAllFields($fieldType->items->fields, $result);
                }
            }
        }

        return $result;
    }

    /**
     * @param string $defaultType
     * @param string $currentType
     * @return bool
     */
    private function isContainedInBiggerType(string $defaultType, string $currentType): bool
    {
        if ($currentType === 'double' && ($defaultType === 'int' || $defaultType === 'float')) {
            return true;
        }

        if ($currentType === 'float' && $defaultType === 'int') {
            return true;
        }

        return false;
    }
}
