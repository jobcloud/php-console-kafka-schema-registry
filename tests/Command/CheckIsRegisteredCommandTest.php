<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\CheckIsRegistredCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Jobcloud\SchemaConsole\Command\CheckIsRegistredCommand
 * @covers \Jobcloud\SchemaConsole\Helper\SchemaFileHelper
 * @covers \Jobcloud\SchemaConsole\Command\AbstractSchemaCommand
 */
class CheckIsRegisteredCommandTest extends AbstractSchemaRegistryTestCase
{
    protected const SCHEMA_TEST_FILE = '/tmp/test.avsc';

    /**
     * @return array
     */
    public function argumentsDataProvider(): array
    {
        return [
            [null, 'Schema does not exist in any version', 1],
            ['1', 'Schema exists in version 1', 0],
            ['2', 'Schema exists in version 2', 0],
            ['3', 'Schema exists in version 3', 0],
            ['999', 'Schema exists in version 999', 0],
        ];
    }

    /**
     * @dataProvider argumentsDataProvider
     * @param string|null $actualVersion
     * @param string $expectedOutput
     * @param int $expectedExitCode
     */
    public function testCommand(?string $actualVersion, string $expectedOutput, int $expectedExitCode): void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'getVersionForSchema' => $actualVersion,
        ]);

        $application = new Application();
        $application->add(new CheckIsRegistredCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:entry:exists');
        $commandTester = new CommandTester($command);

        file_put_contents(self::SCHEMA_TEST_FILE, '{}');

        $commandTester->execute([
            'schemaFile' => self::SCHEMA_TEST_FILE,
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals($expectedOutput, $commandOutput);
        self::assertEquals($expectedExitCode, $commandTester->getStatusCode());
    }
}
