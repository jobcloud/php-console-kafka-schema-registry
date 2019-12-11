<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\SubjectNotFoundException;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\RegisterChangedSchemasCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RegisterChangedSchemasCommandTest extends AbstractSchemaRegistryTestCase
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

    public function testOutputWhenCommandRegisterWithSuccess():void
    {
        $numFiles = 5;
        $this->generateFiles($numFiles);

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'checkSchemaCompatibilityForVersion' => TRUE,
            'getSchemaDefinitionByVersion',
            'getVersionForSchema',
            'registerNewSchemaVersion',
            'getLatestSubjectVersion' => '1',
        ]);

        $schemaRegistryApi
            ->method('getSchemaDefinitionByVersion')
            ->willReturn([])
        ;

        $application = new Application();
        $application->add(new RegisterChangedSchemasCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:register:changed');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertRegExp('/^Successfully registered new version of schema /', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOutputWhenCommandSuccessWithSkipping():void
    {

        $this->generateFiles(5);

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'checkSchemaCompatibilityForVersion' => TRUE,
            'getVersionForSchema' => '1',
            'registerNewSchemaVersion',
            'getLatestSubjectVersion' => '1'
        ]);

        $application = new Application();
        $application->add(new RegisterChangedSchemasCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:register:changed');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('Schema test.schema.1 has been skipped (no change)', $commandOutput);
        self::assertStringContainsString('Schema test.schema.2 has been skipped (no change)', $commandOutput);
        self::assertStringContainsString('Schema test.schema.3 has been skipped (no change)', $commandOutput);
        self::assertStringContainsString('Schema test.schema.4 has been skipped (no change)', $commandOutput);
        self::assertStringContainsString('Schema test.schema.5 has been skipped (no change)', $commandOutput);

        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOutputWhenCommandSuccessWithAllNew():void
    {

        $this->generateFiles(5);

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'checkSchemaCompatibilityForVersion' => TRUE,
            'getVersionForSchema' => '1',
            'registerNewSchemaVersion',
            'getLatestSubjectVersion' => new SubjectNotFoundException()
        ]);

        $application = new Application();
        $application->add(new RegisterChangedSchemasCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:register:changed');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('Successfully registered new version of schema test.schema.1', $commandOutput);
        self::assertStringContainsString('Successfully registered new version of schema test.schema.2', $commandOutput);
        self::assertStringContainsString('Successfully registered new version of schema test.schema.3', $commandOutput);
        self::assertStringContainsString('Successfully registered new version of schema test.schema.4', $commandOutput);
        self::assertStringContainsString('Successfully registered new version of schema test.schema.5', $commandOutput);

        self::assertStringContainsString('test.schema.1 with new version: 1', $commandOutput);
        self::assertStringContainsString('test.schema.2 with new version: 1', $commandOutput);
        self::assertStringContainsString('test.schema.3 with new version: 1', $commandOutput);
        self::assertStringContainsString('test.schema.4 with new version: 1', $commandOutput);
        self::assertStringContainsString('test.schema.5 with new version: 1', $commandOutput);

        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testOutputWhenCommandFailsRegisteringASchema():void
    {
        $this->generateFiles(1, 'asdf');

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'checkSchemaCompatibilityForVersion' => TRUE,
            'getVersionForSchema' => null,
            'registerNewSchemaVersion',
            'getLatestSubjectVersion' => '1'
        ]);

        $application = new Application();
        $application->add(new RegisterChangedSchemasCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:register:changed');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('Skipping test.schema.1 for now because  is not a schema we know about.', $commandOutput);

        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testOutputTotalFailDueToIncompatibility():void
    {
        $this->generateFiles(5);

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'checkSchemaCompatibilityForVersion' => FALSE,
            'getSchemaDefinitionByVersion',
            'registerNewSchemaVersion',
            'getLatestSubjectVersion' => '1',
            'getVersionForSchema' => null
        ]);

        $application = new Application();
        $application->add(new RegisterChangedSchemasCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:register:changed');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaDirectory' => self::SCHEMA_DIRECTORY
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('has an incompatible change', $commandOutput);

        self::assertEquals(1, $commandTester->getStatusCode());
    }
}
