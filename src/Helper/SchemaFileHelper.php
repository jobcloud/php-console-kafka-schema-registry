<?php

namespace Jobcloud\SchemaConsole\Helper;

use AvroSchema;
use AvroSchemaParseException;
use RuntimeException;

class SchemaFileHelper
{

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
}
