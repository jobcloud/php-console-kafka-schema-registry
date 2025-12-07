<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Jobcloud\SchemaConsole\Command\AbstractSchemaCommand;
use Jobcloud\SchemaConsole\Command\SetReadOnlyModeCommand;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(SetReadOnlyModeCommand::class)]
#[CoversClass(SchemaFileHelper::class)]
#[CoversClass(AbstractSchemaCommand::class)]
class SetReadOnlyModeCommandTest extends AbstractSchemaRegistryTestCase
{
    /**
     * @return MockObject|KafkaSchemaRegistryApiClientInterface
     */
    private function getFakeClient(): MockObject
    {
        return $this
            ->getMockBuilder(KafkaSchemaRegistryApiClientInterface::class)
            ->getMock();
    }

    public function testCommandSuccess(): void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClientInterface $schemaRegistryApi */
        $schemaRegistryApi = $this->getFakeClient();

        $schemaRegistryApi
            ->expects(self::once())
            ->method('setImportMode')
            ->with(KafkaSchemaRegistryApiClientInterface::MODE_READONLY)
            ->willReturn(true);

        $application = new Application();
        $application->addCommand(new SetReadOnlyModeCommand($schemaRegistryApi));

        $command = $application->find('kafka-schema-registry:set:mode:readonly');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertSame(
            sprintf("Import mode set to %s", KafkaSchemaRegistryApiClientInterface::MODE_READONLY),
            $commandOutput
        );
        self::assertSame(0, $commandTester->getStatusCode());
    }

    public function testCommandFail(): void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClientInterface $schemaRegistryApi */
        $schemaRegistryApi = $this
            ->getMockBuilder(KafkaSchemaRegistryApiClientInterface::class)
            ->getMock();

        $schemaRegistryApi
            ->expects(self::once())
            ->method('setImportMode')
            ->with(KafkaSchemaRegistryApiClientInterface::MODE_READONLY)
            ->willReturn(false);

        $application = new Application();
        $application->addCommand(new SetReadOnlyModeCommand($schemaRegistryApi));

        $command = $application->find('kafka-schema-registry:set:mode:readonly');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertSame('', $commandOutput);
        self::assertSame(1, $commandTester->getStatusCode());
    }
}
