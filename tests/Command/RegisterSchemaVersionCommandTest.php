<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\RegisterSchemaVersionCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Jobcloud\SchemaConsole\Command\RegisterSchemaVersionCommand
 * @covers \Jobcloud\SchemaConsole\Helper\SchemaFileHelper
 * @covers \Jobcloud\SchemaConsole\Command\AbstractSchemaCommand
 */
class RegisterSchemaVersionCommandTest extends AbstractSchemaRegistryTestCase
{
    protected const SCHEMA_TEST_FILE = '/tmp/test.avsc';

    public function testCommand():void
    {
        file_put_contents(self::SCHEMA_TEST_FILE,
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
EOF);

        $expectedId = '12345abcdefg';

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'registerNewSchemaVersion' => ['id' => $expectedId],
        ]);

        $application = new Application();
        $application->add(new RegisterSchemaVersionCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:register:version');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaFile' => self::SCHEMA_TEST_FILE
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(
            implode(
                PHP_EOL,
                [
                    'Add new schema version to registry',
                    sprintf('Successfully registered new schema with id: %d', $expectedId),
                ]
            ),
            $commandOutput
        );

        self::assertEquals(0, $commandTester->getStatusCode());
    }
}
