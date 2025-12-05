<?php

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckAllSchemaTemplatesDefaultTypeCommand extends Command
{
    private const array TYPE_MAP = [
        "null" => "null",
        "boolean" => "boolean",
        "integer" => "int",
        "string" => "string",
        "double" => "double",
        "array" => "array",
    ];

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:check:template:default:type:all')
            ->setDescription('Checks if default type is the first type in union for all schema templates in folder')
            ->setHelp('Checks if default type is the first type in union for all schema templates in folder')
            ->addArgument(
                'schemaTemplateDirectory',
                InputArgument::REQUIRED,
                'Path to avro schema template directory'
            );
    }

    #[\Override]
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $directory */
        $directory = $input->getArgument('schemaTemplateDirectory');
        $avroFiles = SchemaFileHelper::getAvroFiles($directory);

        $io = new SymfonyStyle($input, $output);

        $failed = [];

        if (false === $this->checkSchemas($avroFiles, $failed)) {
            $io->error('Following schema templates have invalid default value types:');
            $io->listing($failed);

            return 1;
        }

        $io->success('All schema templates have valid default value types');

        return 0;
    }

    /**
     * @param array<string, string> $avroFiles
     * @param array<mixed> $failed
     */
    private function checkSchemas(array $avroFiles, array &$failed = []): bool
    {
        $failed = [];

        foreach ($avroFiles as $avroFile) {
            /** @var string $localSchema */
            $localSchema = file_get_contents($avroFile);

            $invalidFields = $this->checkDefaultType($localSchema);

            foreach ($invalidFields as $invalidField) {
                $failed[] = $invalidField;
            }
        }

        return 0 === count($failed);
    }

    /**
     * @return array<int|string, mixed>
     */
    private function checkDefaultType(string $localSchema): array
    {
        $decodedSchema = json_decode($localSchema);
        if (!property_exists($decodedSchema, 'fields')) {
            return [];
        }

        return $this->checkAllFields($decodedSchema);
    }

    /**
     * @param array<mixed, mixed> $defaultFields
     * @return array<int|string, mixed>
     */
    private function checkAllFields(mixed $decodedSchema, array $defaultFields = []): array
    {
        foreach ($decodedSchema->fields as $field) {
            if (!property_exists($field, 'default')) {
                continue;
            }

            $defaultFields[$field->name] = $this->getFieldName($decodedSchema, $field);

            $fieldTypes = $field->type;

            if (!is_array($fieldTypes)) {
                $fieldTypes = [$fieldTypes];
            }

            if (count($fieldTypes)) {
                $defaultFields = $this->checkSingleField($fieldTypes[0], $field, $defaultFields);
            }
        }

        return $defaultFields;
    }

    /**
     * @param array<mixed, mixed> $defaultFields
     * @return array<int|string, mixed>
     */
    private function checkSingleField(mixed $fieldType, mixed $field, array $defaultFields): array
    {
        $defaultType = strtolower(gettype($field->default));

        if (is_string($fieldType) && isset(self::TYPE_MAP[$defaultType])) {
            if (
                self::TYPE_MAP[$defaultType] === $fieldType
                || $this->isContainedInBiggerType(self::TYPE_MAP[$defaultType], $fieldType)
            ) {
                unset($defaultFields[$field->name]);
            }
        }

        if (
            property_exists($fieldType, 'type')
            && $fieldType->type === 'array'
            && isset(self::TYPE_MAP[$defaultType])
        ) {
            if (self::TYPE_MAP[$defaultType] === $fieldType->type) {
                unset($defaultFields[$field->name]);
            }
        }

        return $defaultFields;
    }

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

    private function getFieldName(mixed $decodedSchema, mixed $field): string
    {
        return $decodedSchema->namespace . '.' . $decodedSchema->name . '.' . $field->name;
    }
}
