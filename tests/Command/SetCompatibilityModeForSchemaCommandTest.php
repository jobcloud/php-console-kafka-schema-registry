<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\AbstractSchemaCommand;
use Jobcloud\SchemaConsole\Command\SetCompatibilityModeForSchemaCommand;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(SetCompatibilityModeForSchemaCommand::class)]
#[CoversClass(SchemaFileHelper::class)]
#[CoversClass(AbstractSchemaCommand::class)]
class SetCompatibilityModeForSchemaCommandTest extends AbstractSchemaRegistryTestCase
{
    public function testCommandWhenCompatibilityIsChanged(): void
    {
        $schemaName = 'SomeSchemaName';

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'setSubjectCompatibilityLevel' => true,
        ]);

        $application = new Application();
        $application->addCommand(new SetCompatibilityModeForSchemaCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:set:schema:compatibility:mode');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaName' => $schemaName,
            'compatibilityLevel' => 'BACKWARD_TRANSITIVE',
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(
            sprintf('Successfully changed compatibility mode for schema: %s', $schemaName),
            $commandOutput
        );
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testCommandWhenCompatibilityIsNotChanged(): void
    {
        $schemaName = 'SomeSchemaName';
        $errorMessage = 'error';

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'setSubjectCompatibilityLevel' => new \Exception($errorMessage),
        ]);

        $application = new Application();
        $application->addCommand(new SetCompatibilityModeForSchemaCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:set:schema:compatibility:mode');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaName' => $schemaName,
            'compatibilityLevel' => 'BACKWARD_TRANSITIVE',
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(
            sprintf('Could not change compatibility mode for schema %s: %s', $schemaName, $errorMessage),
            $commandOutput
        );
        self::assertEquals(1, $commandTester->getStatusCode());
    }
}
