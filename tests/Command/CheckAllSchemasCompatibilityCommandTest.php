<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\SchemaConsole\Command\CheckAllSchemasCompatibilityCommand;
use Jobcloud\SchemaConsole\SchemaRegistryApi;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CheckAllSchemasCompatibilityCommandTest extends AbstractSchemaRegistryTestCase
{
    protected const SCHEMA_DIRECTORY = '/tmp/testSchemas';

    protected const DUMMY_SCHEMA = <<<EOF
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
              "name": "number1",
              "type": "int"
            },
            {
              "name": "number2",
              "type": "float"
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
     * @param string $contents
     */
    protected function generateFiles(int $numberOfFiles, string $contents = self::DUMMY_SCHEMA): void {
        $numbers = range(1,$numberOfFiles);

        array_walk($numbers , static function ($item) use ($contents) {
            file_put_contents(
                sprintf('%s/test.schema.%d.avsc', self::SCHEMA_DIRECTORY, $item),
                $contents
            );
        });

        file_put_contents(
            sprintf('%s/test.txt', self::SCHEMA_DIRECTORY),
            'bla'
        );
    }

    public function testOutputWhenAllCompatible():void
    {
        $this->generateFiles(5);

        /** @var MockObject|SchemaRegistryApi $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(SchemaRegistryApi::class, [
            'checkSchemaCompatibilityForVersion' => TRUE,
            'getSchemaByVersion',
            'getLatestSchemaVersion' => '1'
        ]);

        $schemaRegistryApi
            ->method('getSchemaByVersion')
            ->willReturn('{}')
        ;

        $application = new Application();
        $application->add(new CheckAllSchemasCompatibilityCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:check:compatibility:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertContains('All schemas are compatible', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOutputWhenAllNotCompatible():void
    {
        $this->generateFiles(5);

        /** @var MockObject|SchemaRegistryApi $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(SchemaRegistryApi::class, [
            'checkSchemaCompatibilityForVersion' => false,
            'getSchemaByVersion',
            'getLatestSchemaVersion' => '1'
        ]);

        $schemaRegistryApi
            ->method('getSchemaByVersion')
            ->willReturn('{}')
        ;

        $application = new Application();
        $application->add(new CheckAllSchemasCompatibilityCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:check:compatibility:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertContains('Following schemas are not compatible', $commandOutput);
        self::assertContains('* test.schema.1', $commandOutput);
        self::assertContains('* test.schema.2', $commandOutput);
        self::assertContains('* test.schema.3', $commandOutput);
        self::assertContains('* test.schema.4', $commandOutput);
        self::assertContains('* test.schema.5', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }
}
