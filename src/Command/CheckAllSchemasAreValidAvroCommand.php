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

                $invalidFields = $this->checkDefaultType($localSchema);

                if (count($invalidFields)) {
                    foreach ($invalidFields as $invalidField) {
                        $failed[] = $invalidField;
                    }
                }
            } catch (AvroSchemaParseException $e) {
                $failed[] = $schemaName;
                continue;
            }
        }

        return 0 === count($failed);
    }

    /**
     * @param string $localSchema
     * @return array<string, mixed>
     * @throws AvroSchemaParseException
     */
    private function checkDefaultType(string $localSchema): array
    {
        $decodedSchema = json_decode($localSchema);
        if (!property_exists($decodedSchema, 'fields')) {
            return [];
        }

        $result = [
            'found' => [],
            'default' => [],
        ];

        $result = $this->checkAllFields($decodedSchema, $result);

        return array_diff($result['default'], $result['found']);
    }

    /**
     * @param mixed $decodedSchema
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     * @throws AvroSchemaParseException
     */
    private function checkAllFields($decodedSchema, array $result): array
    {
        foreach ($decodedSchema->fields as $field) {
            $fieldTypes = $field->type;
            if (property_exists($field, 'default')) {
                $result['default'][] = $this->getFieldName($decodedSchema, $field);
            }

            if (is_array($fieldTypes)) {
                foreach ($fieldTypes as $fieldType) {
                    $result = $this->checkSingleField($fieldType, $field, $decodedSchema, $result);
                }
            }

            if (!is_array($fieldTypes)) {
                $result = $this->checkSingleField($fieldTypes, $field, $decodedSchema, $result);
            }
        }

        return $result;
    }

    /**
     * @param mixed $fieldType
     * @param mixed $field
     * @param mixed $decodedSchema
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     * @throws AvroSchemaParseException
     */
    private function checkSingleField($fieldType, $field, $decodedSchema, array $result): array
    {
        $defaultType = null;

        if (property_exists($field, 'default')) {
            $defaultType = strtolower(gettype($field->default));

            if (is_string($fieldType)) {
                if (
                    self::TYPE_MAP[$defaultType] === $fieldType
                    || $this->isContainedInBiggerType(self::TYPE_MAP[$defaultType], $fieldType)
                ) {
                    $result['found'][] = $this->getFieldName($decodedSchema, $field);
                }
            }
        }

        if (property_exists($fieldType, 'type')) {
            if ($fieldType->type === 'record') {
                $result = $this->checkAllFields($fieldType, $result);
            }

            if ($fieldType->type === 'array') {
                if (is_string($defaultType) && self::TYPE_MAP[$defaultType] === $fieldType->type) {
                    $result['found'][] = $this->getFieldName($decodedSchema, $field);
                }

                if (!is_array($fieldType->items) && property_exists($fieldType->items, "type")) {
                    $result = $this->checkAllFields($fieldType->items, $result);
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

    /**
     * @param mixed $decodedSchema
     * @param mixed $field
     * @return string
     */
    private function getFieldName($decodedSchema, $field): string
    {
        return $decodedSchema->namespace . '.' . $decodedSchema->name . '.' . $field->name;
    }
}
