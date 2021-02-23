<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\GetCompatibilityModeCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Jobcloud\SchemaConsole\Command\GetCompatibilityModeCommand
 * @covers \Jobcloud\SchemaConsole\Helper\SchemaFileHelper
 * @covers \Jobcloud\SchemaConsole\Command\AbstractSchemaCommand
 */
class GetCompatibilityModeCommandTest extends AbstractSchemaRegistryTestCase
{
    public function testCommand(): void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'getDefaultCompatibilityLevel' => 'BACKWARD'
        ]);

        $application = new Application();
        $application->add(new GetCompatibilityModeCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:get:compatibility:mode');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals('The registry\'s default compatibility mode is BACKWARD', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }
}
