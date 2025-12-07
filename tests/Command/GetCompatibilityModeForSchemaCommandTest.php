<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\AbstractSchemaCommand;
use Jobcloud\SchemaConsole\Command\GetCompatibilityModeForSchemaCommand;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(GetCompatibilityModeForSchemaCommand::class)]
#[CoversClass(SchemaFileHelper::class)]
#[CoversClass(AbstractSchemaCommand::class)]
class GetCompatibilityModeForSchemaCommandTest extends AbstractSchemaRegistryTestCase
{
    public function testCommand(): void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'getSubjectCompatibilityLevel' => 'BACKWARD',
        ]);

        $application = new Application();
        $application->addCommand(new GetCompatibilityModeForSchemaCommand($schemaRegistryApi));

        $command = $application->find('kafka-schema-registry:get:schema:compatibility:mode');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaName' => 'SomeSchemaName',
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertSame('The schema\'s compatibility mode is BACKWARD', $commandOutput);
        self::assertSame(0, $commandTester->getStatusCode());
    }
}
