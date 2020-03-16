<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\Exception\ImportException;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Jobcloud\SchemaConsole\Command\SetImportModeReadOnlyCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class SetImportModeReadOnlyCommandTest extends AbstractSchemaRegistryTestCase
{
    /**
     * @return MockObject|KafkaSchemaRegistryApiClientInterface
     */
    private function getFakeClient(): MockObject
    {
        return $this
            ->getMockBuilder(KafkaSchemaRegistryApiClientInterface::class)
            ->onlyMethods(['setImportMode'])
            ->getMockForAbstractClass();
    }

    public function testCommandSuccess():void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClientInterface $schemaRegistryApi */
        $schemaRegistryApi = $this->getFakeClient();

        $schemaRegistryApi
            ->expects(self::once())
            ->method('setImportMode')
            ->with(KafkaSchemaRegistryApiClientInterface::MODE_READONLY);

        $application = new Application();
        $application->add(new SetImportModeReadOnlyCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:set:mode:readonly');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(
            sprintf("Import mode set to %s", KafkaSchemaRegistryApiClientInterface::MODE_READONLY), $commandOutput
        );
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testCommandFail():void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClientInterface $schemaRegistryApi */
        $schemaRegistryApi = $this
            ->getMockBuilder(KafkaSchemaRegistryApiClientInterface::class)
            ->onlyMethods(['setImportMode'])
            ->getMockForAbstractClass();

        $schemaRegistryApi
            ->expects(self::once())
            ->method('setImportMode')
            ->with(KafkaSchemaRegistryApiClientInterface::MODE_READONLY)
            ->willThrowException(new ImportException());

        $application = new Application();
        $application->add(new SetImportModeReadOnlyCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:set:mode:readonly');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(null, $commandOutput);
        self::assertEquals(1, $commandTester->getStatusCode());
    }
}