<?php

namespace Jobcloud\SchemaConsole\Tests;

use AvroSchema;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SchemaFileHelperTest extends TestCase
{
    /**
     *
     */
    private const SCHEMA_FILE = '/tmp/test.avsc';

    /**
     * Setting up test prerequisites
     */
    protected function setUp(): void
    {
        parent::setUp();
        file_put_contents(self::SCHEMA_FILE,
<<<EOF
{
  "type": "record",
  "name": "evolution",
  "namespace": "com.landoop",
  "doc": "This is a sample Avro schema to get you started. Please edit",
  "fields": [
    {
      "name": "name",
      "type": "string"
    },
    {
      "name": "number1",
      "type": "int"
    },
    {
      "name": "number2",
      "type": "float"
    }
  ]
}
EOF
        );
    }

    /**
     * Removing test requisites
     */
    protected function tearDown(): void
    {
        if(file_exists(self::SCHEMA_FILE)) {
            unlink(self::SCHEMA_FILE);
        }
    }

    public function testReadAvroSchemaFromFile():void {
        $contents = SchemaFileHelper::readAvroSchemaFromFile(self::SCHEMA_FILE);

        self::assertInstanceOf(AvroSchema::class, $contents);
    }

    public function testReadSchemaFromFile():void {
        $contents = json_decode(SchemaFileHelper::readSchemaFromFile(self::SCHEMA_FILE), true);

        self::assertArrayHasKey('fields', $contents);
        self::assertArrayHasKey('type', $contents);
        self::assertArrayHasKey('name', $contents);
    }

    public function testReadSchemaFromFileFail():void {
        self::expectException(RuntimeException::class);
        SchemaFileHelper::readSchemaFromFile('/tmp/non-existent-file.avsc');
    }

    public function testGetSchemaName(): void {
        self::assertEquals('test', SchemaFileHelper::getSchemaName(self::SCHEMA_FILE));
    }

}