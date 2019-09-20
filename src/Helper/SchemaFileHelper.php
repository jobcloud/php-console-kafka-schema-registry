<?php

namespace Jobcloud\SchemaConsole\Helper;

use AvroSchema;
use RuntimeException;

class SchemaFileHelper
{

    /**
     * @param string $filePath
     * @return AvroSchema
     * @throws \AvroSchemaParseException
     */
    public static function readAvroSchemaFromFile(string $filePath): AvroSchema {

        if(!is_readable($filePath)) {
            throw new RuntimeException(
                sprintf('Cannot access file %s. Check file path and/or file permissions', $filePath)
            );
        }

        $filePath = realpath($filePath);

        return AvroSchema::parse(file_get_contents($filePath));
    }

    /**
     * @param string $filePath
     * @return string
     */
    public static function readSchemaName(string $filePath): string
    {
        return strtolower(rtrim(basename($filePath), '.json'));
    }
}