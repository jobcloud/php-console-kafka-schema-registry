<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\SchemaConsole\Command\CheckAllSchemasAreValidAvroCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Jobcloud\SchemaConsole\Command\CheckAllSchemasAreValidAvroCommand
 * @covers \Jobcloud\SchemaConsole\Helper\SchemaFileHelper
 */
class CheckAllSchemasAreValidAvroCommandTestTest extends AbstractSchemaRegistryTestCase
{
    protected const SCHEMA_DIRECTORY = '/tmp/testSchemas';

    protected const GOOD_SCHEMA = <<<EOF
        {
          "type": "record",
          "name": "test",
          "namespace": "ch.jobcloud",
          "doc": "This is a sample Avro schema to get you started. Please edit",
          "fields": [
            {
              "name": "name",
              "type": "string"
            },
            {
              "name": "name2",
              "type": ["null","float"],
              "default": null
            },
            {
              "name": "name3",
              "type": ["null","string"],
              "default": ""
            },
            {
              "name": "bool1",
              "type": ["null","boolean"],
              "default": false
            },
            {
              "name": "number1",
              "type": "int"
            },
            {
              "name": "number2",
              "type": "float"
            },
            {
              "name": "number3",
              "type": "int",
              "default": 0
            },
            {
              "name": "number4",
              "type": ["double","float"],
              "default": 0.5
            }
          ]
        }
        EOF;

    protected const BAD_SCHEMA = <<<EOF
        {
          "type: "record",
          "name": "test",
          "namespace": "ch.jobcloud",
          "doc: "This is a sample Avro schema to get you started. Please edit",
          "fields": [
            {
              "name": "name",
              "type": "string"
            },
            {
              "name": "name2",
              "type": "string",
              "default": null
            },
            {
              "name": "name3",
              "type": "null",
              "default": ""
            },
            {
              "name": "bool1",
              "type": ["null","string"],
              "default": false
            },
            {
              "name": "number1",
              "type": "int"
            },
            {
              "name": "number2",
              "type": "float"
            },
            {
              "name": "number3",
              "type": "double",
              "default": 0
            },
            {
              "name": "number4",
              "type": "int",
              "default": 0.5
            }
          ]
        }
        EOF;

    protected const KEY_SCHEMA = <<<EOF
        {
          "type": "string"
        }
        EOF;

    protected const DEFAULT_VALUE_GOOD_SCHEMA_NESTED = <<<EOF
        {
            "type": "record",
            "name": "test",
            "namespace": "ch.jobcloud",
            "fields": [
                {
                    "name": "array1",
                    "type": [
                        "null",
                        {
                            "type": "array",
                            "items": "string"
                        }
                    ],
                    "default": []
                },
                {
                    "name": "name1",
                    "type": [
                        "null",
                        "string"
                    ],
                    "default": null
                },
                {
                    "name": "name2",
                    "type": [
                        "null",
                        {
                            "type": "record",
                            "name": "demo",
                            "namespace": "ch.jobcloud",
                            "fields": [
                                {
                                    "name": "name",
                                    "type": [
                                        "null",
                                        "string"
                                    ],
                                    "default": null
                                },
                                {
                                    "name": "name3",
                                    "type": [
                                        "null",
                                        {
                                            "type": "array",
                                            "items": {
                                                "type": "record",
                                                "name": "example",
                                                "namespace": "ch.jobcloud",
                                                "fields": [
                                                    {
                                                        "name": "name4",
                                                        "type": "string"
                                                    },
                                                    {
                                                        "name": "number",
                                                        "type": "float",
                                                        "default": 0
                                                    }
                                                ]
                                            }
                                        }
                                    ],
                                    "default": []
                                }
                            ]
                        }
                    ],
                    "default": null
                },
                {
                  "name": "number1",
                  "type": "double",
                  "default": 0
                },
                {
                  "name": "benefits",
                  "type": {
                    "type": "array",
                    "items": "string"
                  },
                  "default": [],
                  "doc": "List of id's of related benefits which the company provides"
                },
                {
                  "name": "number2",
                  "type": "double",
                  "default": 0.0
                }
            ]
        }
        EOF;

    protected const DEFAULT_VALUE_BAD_SCHEMA_NESTED = <<<EOF
        {
            "type": "record",
            "name": "test",
            "namespace": "ch.jobcloud",
            "fields": [
                {
                    "name": "array1",
                    "type": [
                        "null",
                        {
                            "type": "array",
                            "items": "string"
                        }
                    ],
                    "default": []
                },
                {
                    "name": "name1",
                    "type": [
                        "null",
                        "string"
                    ],
                    "default": null
                },
                {
                    "name": "name2",
                    "type": [
                        "null",
                        {
                            "type": "record",
                            "name": "demo",
                            "namespace": "ch.jobcloud",
                            "fields": [
                                {
                                    "name": "name",
                                    "type": [
                                        "null",
                                        "string"
                                    ],
                                    "default": null
                                },
                                {
                                    "name": "name3",
                                    "type": [
                                        "null",
                                        {
                                            "type": "array",
                                            "items": {
                                                "type": "record",
                                                "name": "example",
                                                "namespace": "ch.jobcloud",
                                                "fields": [
                                                    {
                                                        "name": "name4",
                                                        "type": "string"
                                                    },
                                                    {
                                                        "name": "number",
                                                        "type": "float",
                                                        "default": 0
                                                    }
                                                ]
                                            }
                                        }
                                    ],
                                    "default": []
                                }
                            ]
                        }
                    ],
                    "default": null
                },
                {
                  "name": "number1",
                  "type": "double",
                  "default": 0
                },
                {
                  "name": "benefits",
                  "type": {
                    "type": "array",
                    "items": "string"
                  },
                  "default": [],
                  "doc": "List of id's of related benefits which the company provides"
                },
                {
                  "name": "number2",
                  "type": "double",
                  "default": null
                }
            ]
        }
        EOF;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        if (!file_exists(self::SCHEMA_DIRECTORY)){
            mkdir(self::SCHEMA_DIRECTORY);
        }
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists(self::SCHEMA_DIRECTORY)){
            array_map('unlink', glob(self::SCHEMA_DIRECTORY . '/*.*'));
            rmdir(self::SCHEMA_DIRECTORY);
        }
    }

    /**
     * @param int $numberOfFiles
     * @param bool $makeBad
     */
    protected function generateFiles(int $numberOfFiles, bool $makeBad = false): void {
        $numbers = range(1,$numberOfFiles);

        if($makeBad) {
            file_put_contents(
                sprintf('%s/test.schema.bad1.avsc', self::SCHEMA_DIRECTORY),
                self::BAD_SCHEMA
            );

            file_put_contents(
                sprintf('%s/test.schema.bad2.avsc', self::SCHEMA_DIRECTORY),
                self::BAD_SCHEMA
            );
        }

        array_walk($numbers , static function ($item) {
            file_put_contents(
                sprintf('%s/test.schema.%d.avsc', self::SCHEMA_DIRECTORY, $item),
                self::GOOD_SCHEMA
            );
        });
    }

    public function testOutputWhenAllValid():void
    {
        $this->generateFiles(5);

        $application = new Application();
        $application->add(new CheckAllSchemasAreValidAvroCommand());
        $command = $application->find('kafka-schema-registry:check:valid:avro:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('All schemas are valid Avro', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOutputWithDefaultTypeParsingError():void
    {
        file_put_contents(
            sprintf('%s/test.schema.default.avsc', self::SCHEMA_DIRECTORY),
            self::DEFAULT_VALUE_BAD_SCHEMA_NESTED
        );

        $application = new Application();
        $application->add(new CheckAllSchemasAreValidAvroCommand());
        $command = $application->find('kafka-schema-registry:check:valid:avro:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('Following schemas are not valid Avro', $commandOutput);
        self::assertStringContainsString('ch.jobcloud.test.number2', $commandOutput);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testOutputWithDefaultTypeParsingNestedSchema():void
    {
        file_put_contents(
            sprintf('%s/test.schema.default.avsc', self::SCHEMA_DIRECTORY),
            self::DEFAULT_VALUE_GOOD_SCHEMA_NESTED
        );

        $application = new Application();
        $application->add(new CheckAllSchemasAreValidAvroCommand());
        $command = $application->find('kafka-schema-registry:check:valid:avro:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('All schemas are valid Avro', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOutputWithKeySchema():void
    {
        file_put_contents(
            sprintf('%s/test.schema.key.avsc', self::SCHEMA_DIRECTORY),
            self::KEY_SCHEMA
        );

        $application = new Application();
        $application->add(new CheckAllSchemasAreValidAvroCommand());
        $command = $application->find('kafka-schema-registry:check:valid:avro:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('All schemas are valid Avro', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOutputWhenAllNotInvalid():void
    {
        $this->generateFiles(5, true);

        $application = new Application();
        $application->add(new CheckAllSchemasAreValidAvroCommand());
        $command = $application->find('kafka-schema-registry:check:valid:avro:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('Following schemas are not valid Avro', $commandOutput);
        self::assertStringContainsString('* test.schema.bad1', $commandOutput);
        self::assertStringContainsString('* test.schema.bad2', $commandOutput);
        self::assertEquals(1, $commandTester->getStatusCode());
    }
}
