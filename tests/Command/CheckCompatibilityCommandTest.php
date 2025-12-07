<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\AbstractSchemaCommand;
use Jobcloud\SchemaConsole\Command\CheckCompatibilityCommand;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(CheckCompatibilityCommand::class)]
#[CoversClass(SchemaFileHelper::class)]
#[CoversClass(AbstractSchemaCommand::class)]
class CheckCompatibilityCommandTest extends AbstractSchemaRegistryTestCase
{
    protected const string SCHEMA_TEST_FILE = '/tmp/test.avsc';

    /**
     * @return array
     */
    public static function argumentsDataProvider(): array
    {
        return [
            [true, 1, 'Schema is Compatible', 0],
            [true, '1', 'Schema is Compatible', 0],
            [false, 1, 'Schema is NOT Compatible', 1],
            [false, '1', 'Schema is NOT Compatible', 1],
        ];
    }

    #[DataProvider('argumentsDataProvider')]
    public function testCommand(
        bool $actualCompatible,
        mixed $versionArgument,
        string $expectedOutput,
        int $expectedExitCode
    ): void {
        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'checkSchemaCompatibilityForVersion' => $actualCompatible,
        ]);

        $application = new Application();
        $application->addCommand(new CheckCompatibilityCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:check:compatibility');
        $commandTester = new CommandTester($command);

        file_put_contents(self::SCHEMA_TEST_FILE, '{}');

        $commandTester->execute([
            'schemaFile' => self::SCHEMA_TEST_FILE,
            'schemaVersion' => $versionArgument,
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals($expectedOutput, $commandOutput);
        self::assertEquals($expectedExitCode, $commandTester->getStatusCode());
    }
}
