<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\SchemaConsole\Command\CheckAllSchemaTemplatesNameFieldsCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Jobcloud\SchemaConsole\Command\CheckAllSchemaTemplatesNameFieldsCommand
 */
class CheckAllSchemaTemplatesNameFieldsCommandTest extends AbstractSchemaRegistryTestCase
{
    protected const SCHEMA_DIRECTORY = '/tmp/testSchemas';

    protected const GOOD_RECORD_SCHEMA = <<<EOF
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
            }
          ]
        }
        EOF;

    protected const GOOD_ENUM_SCHEMA = <<<EOF
        {
          "type": "enum",
          "name": "Suit",
          "namespace": "ch.jobcloud",
          "symbols" : ["SPADES", "HEARTS", "DIAMONDS", "CLUBS"]
        }
        EOF;

    protected const GOOD_FIXED_SCHEMA = <<<EOF
        {
          "type": "fixed",
          "name": "md5",
          "namespace": "ch.jobcloud",
          "size" : 16
        }
        EOF;

    protected const BAD_SCHEMA = <<<EOF
        {
          "type": "record",
          "name": "000test",
          "namespace": "ch.jobcloud",
          "doc": "This is a sample Avro schema to get you started. Please edit",
          "fields": [
            {
              "name": "name",
              "type": "string",
              "doc": "some desc"
            }
          ]
        }
        EOF;

    protected const BAD_SCHEMA1 = <<<EOF
        {
          "type": "record",
          "name": "test-schema",
          "namespace": "ch.jobcloud",
          "doc": "This is a sample Avro schema to get you started. Please edit",
          "fields": [
            {
              "name": "name",
              "type": "string",
              "doc": "some desc"
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

    public function testOutputWhenAllValid(): void
    {
        file_put_contents(
            sprintf('%s/test.schema.record.avsc', self::SCHEMA_DIRECTORY),
            self::GOOD_RECORD_SCHEMA
        );

        file_put_contents(
            sprintf('%s/test.schema.enum.avsc', self::SCHEMA_DIRECTORY),
            self::GOOD_ENUM_SCHEMA
        );

        file_put_contents(
            sprintf('%s/test.schema.fixed.avsc', self::SCHEMA_DIRECTORY),
            self::GOOD_FIXED_SCHEMA
        );

        $application = new Application();
        $application->add(new CheckAllSchemaTemplatesNameFieldsCommand());
        $command = $application->find('kafka-schema-registry:check:template:names:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaTemplateDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('All schema templates have valid name fields', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOutputWhenNameStartsWithNumber(): void
    {
        file_put_contents(
            sprintf('%s/test.schema.bad.avsc', self::SCHEMA_DIRECTORY),
            self::BAD_SCHEMA
        );

        $application = new Application();
        $application->add(new CheckAllSchemaTemplatesNameFieldsCommand());
        $command = $application->find('kafka-schema-registry:check:template:names:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaTemplateDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString(
            'A template schema name field must comply with the following AVRO naming conventions',
            $commandOutput
        );
        self::assertStringContainsString('* test.schema.bad', $commandOutput);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testOutputWhenNameContainsDash(): void
    {
        file_put_contents(
            sprintf('%s/test.schema.bad1.avsc', self::SCHEMA_DIRECTORY),
            self::BAD_SCHEMA1
        );

        $application = new Application();
        $application->add(new CheckAllSchemaTemplatesNameFieldsCommand());
        $command = $application->find('kafka-schema-registry:check:template:names:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaTemplateDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString(
            'A template schema name field must comply with the following AVRO naming conventions',
            $commandOutput
        );
        self::assertStringContainsString('* test.schema.bad1', $commandOutput);
        self::assertEquals(1, $commandTester->getStatusCode());
    }
}
