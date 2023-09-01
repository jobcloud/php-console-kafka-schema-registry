<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\DeleteAllSchemasCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Jobcloud\SchemaConsole\Command\DeleteAllSchemasCommand
 * @covers \Jobcloud\SchemaConsole\Helper\SchemaFileHelper
 * @covers \Jobcloud\SchemaConsole\Command\AbstractSchemaCommand
 */
class DeleteAllSchemasCommandTest extends TestCase
{
    public function testCommandSoftDelete(): void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->getMockBuilder(KafkaSchemaRegistryApiClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSubjects', 'deleteSubject'])
            ->getMock();

        $schemaRegistryApi->expects(self::once())->method('getSubjects')->willReturn(['schema1', 'schema2', 'schema3']);

        $schemaRegistryApi->expects(self::exactly(3))->method('deleteSubject')->willReturn([]);

        $application = new Application();
        $application->add(new DeleteAllSchemasCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:delete:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals('All schemas deleted.', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testCommandHardDelete(): void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->getMockBuilder(KafkaSchemaRegistryApiClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSubjects', 'deleteSubject'])
            ->getMock();

        $schemaRegistryApi->expects(self::once())
            ->method('getSubjects')
            ->willReturn(['schema1']);

        $schemaRegistryApi->expects(self::exactly(2))
            ->method('deleteSubject')
            ->with(self::callback(function ($inputArgument){
                static $input = 'schema1';
                if ($inputArgument === $input) {
                    $input = $input.'?permanent=true';
                    return true;
                }
                return false;
            }))
            ->willReturn([]);

        $application = new Application();
        $application->add(new DeleteAllSchemasCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:delete:all');
        $commandTester = new CommandTester($command);

        $commandTester->execute(['--hard' => true]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals('All schemas deleted.', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }
}
