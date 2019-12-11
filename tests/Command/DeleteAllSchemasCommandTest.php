<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\DeleteAllSchemasCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteAllSchemasCommandTest extends AbstractSchemaRegistryTestCase
{

    public function testCommand(): void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'getSubjects' => ['schema1', 'schema2', 'schema3'],
            'deleteSubject'
        ]);

        $application = new Application();
        $application->add(new DeleteAllSchemasCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:delete:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals('All schemas deleted.', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }
}