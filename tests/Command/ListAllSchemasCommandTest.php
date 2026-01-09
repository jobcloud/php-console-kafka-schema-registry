<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\ListAllSchemasCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Jobcloud\SchemaConsole\Command\ListAllSchemasCommand
 * @covers \Jobcloud\SchemaConsole\Helper\SchemaFileHelper
 * @covers \Jobcloud\SchemaConsole\Command\AbstractSchemaCommand
 */
class ListAllSchemasCommandTest extends AbstractSchemaRegistryTestCase
{
    /**
     * @dataProvider inputArgDataProvider
     *
     * @param array<string, string> $inputArg
     */
    public function testCommand(array $inputArg): void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'getSubjects' => [1,2,3,4],
        ]);

        $application = new Application();
        $application->addCommand(new ListAllSchemasCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:list');
        $commandTester = new CommandTester($command);

        $commandTester->execute($inputArg);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(implode(PHP_EOL, [1,2,3,4]), $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function inputArgDataProvider(): array
    {
        return [
            'inputArgDataProvider:1' => [[]],
            'inputArgDataProvider:2' => [['includeDeleted' => 'true']],
            'inputArgDataProvider:3' => [['includeDeleted' => 'false']],
        ];
    }
}
