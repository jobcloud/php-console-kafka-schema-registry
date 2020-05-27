<?php

namespace Jobcloud\SchemaConsole\Helper;

use AvroSchema;
use AvroSchemaParseException;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

class SchemaFileHelper
{
    /** @var string */
    private const FIELDS_FIELD_KEY = 'fields';

    /** @var string */
    private const DOC_FIELD_KEY = 'doc';

    /**
     * @param string $filePath
     * @return AvroSchema
     * @throws AvroSchemaParseException
     */
    public static function readAvroSchemaFromFile(string $filePath): AvroSchema
    {
        return AvroSchema::parse(static::readSchemaFromFile($filePath));
    }

    /**
     * @param string $filePath
     * @return string
     * @throws RuntimeException
     */
    public static function readSchemaFromFile(string $filePath): string
    {

        if (!is_readable($filePath)) {
            throw new RuntimeException(
                sprintf('Cannot access file %s. Check file path and/or file permissions', $filePath)
            );
        }

        return (string) file_get_contents((string) realpath($filePath));
    }

    /**
     * @param string $filePath
     * @return string
     */
    public static function getSchemaName(string $filePath): string
    {
        return basename($filePath, '.' . Avro::FILE_EXTENSION);
    }

    /**
     * @param string $directory
     * @return array
     */
    public static function getAvroFiles(string $directory): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS
            )
        );

        $files = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (Avro::FILE_EXTENSION !== $file->getExtension()) {
                continue;
            }

            $files[$file->getBasename('.' . Avro::FILE_EXTENSION)] = $file->getRealPath();
        }

        return $files;
    }

    /**
     * @param array $schema
     * @return bool
     */
    public static function checkDocCommentsOnSchemaTemplates(array $schema): bool
    {
        $fields = $schema[self::FIELDS_FIELD_KEY] ?? null;

        if (!is_array($fields) || count($fields) === 0) {
            return true;
        }

        foreach ($fields as $field) {
            $doc = $field[self::DOC_FIELD_KEY] ?? null;

            if (!is_string($doc) || trim($doc) === '') {
                return false;
            }
        }

        return true;
    }
}
