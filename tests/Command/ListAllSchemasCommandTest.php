<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\AbstractSchemaCommand;
use Jobcloud\SchemaConsole\Command\ListAllSchemasCommand;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(ListAllSchemasCommand::class)]
#[CoversClass(SchemaFileHelper::class)]
#[CoversClass(AbstractSchemaCommand::class)]
class ListAllSchemasCommandTest extends AbstractSchemaRegistryTestCase
{
    /**
     * @param array<string, string> $inputArg
     */
    #[DataProvider('validInputArgDataProvider')]
    public function testCommandWithValidArgs(array $inputArg): void
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

        self::assertSame(implode(PHP_EOL, [1,2,3,4]), $commandOutput);
        self::assertSame(0, $commandTester->getStatusCode());
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function validInputArgDataProvider(): array
    {
        return [
            'validInputArgDataProvider:1' => [[]],
            'validInputArgDataProvider:2' => [['includeDeleted' => 'true']],
            'validInputArgDataProvider:3' => [['includeDeleted' => 'false']],
        ];
    }

    public function testCommandWithInvalidArg(): void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class);

        $application = new Application();
        $application->addCommand(new ListAllSchemasCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:list');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'includeDeleted' => 'invalidValue',
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(
            'Invalid \'includeDeleted\' argument. Allowed values are \'true\' or \'false\'.',
            $commandOutput
        );
        self::assertEquals(1, $commandTester->getStatusCode());
    }
}
