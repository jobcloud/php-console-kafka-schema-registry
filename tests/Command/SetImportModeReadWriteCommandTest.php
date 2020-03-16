<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Jobcloud\SchemaConsole\Command\SetImportModeReadWriteCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class SetImportModeReadWriteCommandTest extends AbstractSchemaRegistryTestCase
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
            ->with(KafkaSchemaRegistryApiClientInterface::MODE_READWRITE)
            ->willReturn(true);

        $application = new Application();
        $application->add(new SetImportModeReadWriteCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:set:mode:readwrite');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(
            sprintf("Import mode set to %s", KafkaSchemaRegistryApiClientInterface::MODE_READWRITE), $commandOutput
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
            ->with(KafkaSchemaRegistryApiClientInterface::MODE_READWRITE)
            ->willReturn(false);

        $application = new Application();
        $application->add(new SetImportModeReadWriteCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:set:mode:readwrite');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(null, $commandOutput);
        self::assertEquals(1, $commandTester->getStatusCode());
    }
}