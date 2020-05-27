<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\SchemaConsole\Command\CheckAllSchemasDocCommentsCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CheckAllSchemasDocCommentsCommandTest extends AbstractSchemaRegistryTestCase
{
    protected const SCHEMA_DIRECTORY = '/tmp/testSchemas';

    protected const GOOD_SCHEMA = <<<EOF
        {
          "type": "record",
          "name": "test",
          "namespace": "ch.jobcloud",
          "fields": [
            {
              "name": "name",
              "type": "string",
              "doc": "some desc"
            }
          ]
        }
        EOF;

    protected const BAD_SCHEMA = <<<EOF
        {
          "type: "record",
          "name": "test",
          "namespace": "ch.jobcloud",
          "fields": [
            {
              "name": "name",
              "type": "string"
            }
          ]
        }
        EOF;

    protected const BAD_SCHEMA1 = <<<EOF
        {
          "type: "record",
          "name": "test",
          "namespace": "ch.jobcloud"
          "fields": [
            {
              "name": "name",
              "type": "string"
            }
          ]
        }
        EOF;

    protected const BAD_SCHEMA2 = <<<EOF
        {
          "type: "record",
          "name": "test",
          "namespace": "ch.jobcloud"
          "fields": [
            {
              "name": "name",
              "type": "string"
              "doc": " "
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
                sprintf('%s/test.schema.bad.avsc', self::SCHEMA_DIRECTORY),
                self::BAD_SCHEMA
            );

            file_put_contents(
                sprintf('%s/test.schema.bad1.avsc', self::SCHEMA_DIRECTORY),
                self::BAD_SCHEMA1
            );

            file_put_contents(
                sprintf('%s/test.schema.bad2.avsc', self::SCHEMA_DIRECTORY),
                self::BAD_SCHEMA2
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
        $this->generateFiles(1);

        $application = new Application();
        $application->add(new CheckAllSchemasDocCommentsCommand());
        $command = $application->find('kafka-schema-registry:check:doc:comments:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('All schemas have doc comments on all fields', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOutputWhenAllNotInvalid():void
    {
        $this->generateFiles(1, true);

        $application = new Application();
        $application->add(new CheckAllSchemasDocCommentsCommand());
        $command = $application->find('kafka-schema-registry:check:doc:comments:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('Following schemas do not have doc comments on all fields', $commandOutput);
        self::assertStringContainsString('* test.schema.bad', $commandOutput);
        self::assertStringContainsString('* test.schema.bad1', $commandOutput);
        self::assertStringContainsString('* test.schema.bad2', $commandOutput);
        self::assertEquals(1, $commandTester->getStatusCode());
    }
}
