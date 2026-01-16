<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\AbstractSchemaCommand;
use Jobcloud\SchemaConsole\Command\CheckIsRegisteredCommand;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(CheckIsRegistredCommand::class)]
#[CoversClass(SchemaFileHelper::class)]
#[CoversClass(AbstractSchemaCommand::class)]
class CheckIsRegisteredCommandTest extends AbstractSchemaRegistryTestCase
{
    protected const string SCHEMA_TEST_FILE = '/tmp/test.avsc';

    /**
     * @return string[][]|int[][]|null[][]
     */
    public static function argumentsDataProvider(): array
    {
        return [
            [null, 'Schema does not exist in any version', 1],
            ['1', 'Schema exists in version 1', 0],
            ['2', 'Schema exists in version 2', 0],
            ['3', 'Schema exists in version 3', 0],
            ['999', 'Schema exists in version 999', 0],
        ];
    }

    #[DataProvider('argumentsDataProvider')]
    public function testCommand(?string $actualVersion, string $expectedOutput, int $expectedExitCode): void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'getVersionForSchema' => $actualVersion,
        ]);

        $application = new Application();
        $application->addCommand(new CheckIsRegistredCommand($schemaRegistryApi));

        $command = $application->find('kafka-schema-registry:entry:exists');
        $commandTester = new CommandTester($command);

        file_put_contents(self::SCHEMA_TEST_FILE, '{}');

        $commandTester->execute([
            'schemaFile' => self::SCHEMA_TEST_FILE,
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertSame($expectedOutput, $commandOutput);
        self::assertSame($expectedExitCode, $commandTester->getStatusCode());
    }
}
