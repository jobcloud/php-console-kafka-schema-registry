<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\SchemaConsole\Command\DeleteAllSchemasCommand;
use Jobcloud\SchemaConsole\SchemaRegistryApi;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteAllSchemasCommandTest extends AbstractSchemaRegistryTestCase
{

    public function testCommand(): void
    {
        /** @var MockObject|SchemaRegistryApi $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(SchemaRegistryApi::class, [
            'getAllSchemas' => ['schema1', 'schema2', 'schema3'],
            'deleteSchema'
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