<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\SchemaConsole\Command\CheckAllSchemaTemplatesDefaultTypeCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Jobcloud\SchemaConsole\Command\CheckAllSchemaTemplatesDefaultTypeCommand
 * @covers \Jobcloud\SchemaConsole\Helper\SchemaFileHelper
 */
class CheckAllSchemaTemplatesDefaultTypeCommandTest extends AbstractSchemaRegistryTestCase
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
              "type": "string",
              "doc": "some desc"
            },
            {
              "name": "name2",
              "type": ["null","string"],
              "default": "",
              "doc": "some desc"
            },
            {
              "name": "bool1",
              "type": ["null","boolean"],
              "default": false,
              "doc": "some desc"
            },
            {
              "name": "number1",
              "type": "int",
              "doc": "some desc"
            },
            {
              "name": "number2",
              "type": "int",
              "default": 0,
              "doc": "some desc"
            },
            {
              "name": "number3",
              "type": ["double","float"],
              "default": 0.5,
              "doc": "some desc"
            },
            {
              "name": "number3",
              "type": ["double","float"],
              "default": 0,
              "doc": "some desc"
            },
            {
              "name": "array1",
              "type": [
                "null",
                {
                  "type": "array",
                  "items": "ch.jobcloud.item"
                }
              ],
              "default": [],
              "doc": "some desc"
            }
          ]
        }
        EOF;

    protected const BAD_SCHEMA = <<<EOF
        {
          "type": "record",
          "name": "test",
          "namespace": "ch.jobcloud",
          "doc": "This is a sample Avro schema to get you started. Please edit",
          "fields": [
            {
              "name": "name",
              "type": "string",
              "doc": "some desc"
            },
            {
              "name": "name2",
              "type": ["null","string"],
              "default": "",
              "doc": "some desc"
            },
            {
              "name": "bool1",
              "type": ["null","boolean"],
              "default": 0,
              "doc": "some desc"
            },
            {
              "name": "number1",
              "type": "int",
              "doc": "some desc"
            },
            {
              "name": "number2",
              "type": "int",
              "default": null,
              "doc": "some desc"
            },
            {
              "name": "number3",
              "type": ["double","float"],
              "default": 0.5,
              "doc": "some desc"
            }
          ]
        }
        EOF;

    protected const KEY_SCHEMA = <<<EOF
        {
          "type": "string"
        }
        EOF;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        if (!file_exists(self::SCHEMA_DIRECTORY)) {
            mkdir(self::SCHEMA_DIRECTORY);
        }
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists(self::SCHEMA_DIRECTORY)) {
            array_map('unlink', glob(self::SCHEMA_DIRECTORY . '/*.*'));
            rmdir(self::SCHEMA_DIRECTORY);
        }
    }

    /**
     * @param int $numberOfFiles
     * @param bool $makeBad
     */
    protected function generateFiles(int $numberOfFiles, bool $makeBad = false): void
    {
        $numbers = range(1, $numberOfFiles);

        if ($makeBad) {
            file_put_contents(
                sprintf('%s/test.schema.bad1.avsc', self::SCHEMA_DIRECTORY),
                self::BAD_SCHEMA
            );

            file_put_contents(
                sprintf('%s/test.schema.bad2.avsc', self::SCHEMA_DIRECTORY),
                self::BAD_SCHEMA
            );
        }

        array_walk($numbers, static function ($item) {
            file_put_contents(
                sprintf('%s/test.schema.%d.avsc', self::SCHEMA_DIRECTORY, $item),
                self::GOOD_SCHEMA
            );
        });
    }

    public function testOutputWhenAllValid(): void
    {
        $this->generateFiles(5);

        $application = new Application();
        $application->add(new CheckAllSchemaTemplatesDefaultTypeCommand());
        $command = $application->find('kafka-schema-registry:check:template:default:type:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaTemplateDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('All schema templates have valid default value types', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOutputWithKeySchema(): void
    {
        file_put_contents(
            sprintf('%s/test.schema.key.avsc', self::SCHEMA_DIRECTORY),
            self::KEY_SCHEMA
        );

        $application = new Application();
        $application->add(new CheckAllSchemaTemplatesDefaultTypeCommand());
        $command = $application->find('kafka-schema-registry:check:template:default:type:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaTemplateDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('All schema templates have valid default value types', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOutputWhenAllNotInvalid(): void
    {
        $this->generateFiles(5, true);

        $application = new Application();
        $application->add(new CheckAllSchemaTemplatesDefaultTypeCommand());
        $command = $application->find('kafka-schema-registry:check:template:default:type:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaTemplateDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('Following schema templates have invalid default value types', $commandOutput);
        self::assertStringContainsString('* ch.jobcloud.test.bool1', $commandOutput);
        self::assertStringContainsString('* ch.jobcloud.test.number2', $commandOutput);
        self::assertEquals(1, $commandTester->getStatusCode());
    }
}
