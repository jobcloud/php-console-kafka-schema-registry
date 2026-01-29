<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Jobcloud\SchemaConsole\Command\AbstractSchemaCommand;
use Jobcloud\SchemaConsole\Command\SetReadWriteModeCommand;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(SetReadWriteModeCommand::class)]
#[CoversClass(SchemaFileHelper::class)]
#[CoversClass(AbstractSchemaCommand::class)]
class SetReadWriteModeCommandTest extends AbstractSchemaRegistryTestCase
{
    public function testCommandSuccess(): void
    {
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'setImportMode' => true,
        ]);

        $application = new Application();
        $application->addCommand(new SetReadWriteModeCommand($schemaRegistryApi));

        $command = $application->find('kafka-schema-registry:set:mode:readwrite');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertSame(
            sprintf("Import mode set to %s", KafkaSchemaRegistryApiClientInterface::MODE_READWRITE),
            $commandOutput
        );
        self::assertSame(0, $commandTester->getStatusCode());
    }

    public function testCommandFail(): void
    {
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'setImportMode' => false,
        ]);

        $application = new Application();
        $application->addCommand(new SetReadWriteModeCommand($schemaRegistryApi));

        $command = $application->find('kafka-schema-registry:set:mode:readwrite');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertSame('', $commandOutput);
        self::assertSame(1, $commandTester->getStatusCode());
    }
}
