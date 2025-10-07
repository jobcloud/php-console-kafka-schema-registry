<?php

namespace Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\SetAllSchemasCompatibilityModeCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \Jobcloud\SchemaConsole\Command\SetAllSchemasCompatibilityModeCommand
 * @covers \Jobcloud\SchemaConsole\Command\AbstractSchemaCommand
 */
class SetAllSchemasCompatibilityModeCommandTest extends AbstractSchemaRegistryTestCase
{
    public function testCommandWithValidConfigFileAndAllSuccessful(): void
    {
        $configFile = $this->createTempConfigFile([
            ['schemaName' => 'schema1', 'compatibilityLevel' => 'BACKWARD'],
            ['schemaName' => 'schema2', 'compatibilityLevel' => 'FORWARD'],
        ]);

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'setSubjectCompatibilityLevel' => true,
        ]);

        $commandTester = $this->createCommandTester($schemaRegistryApi);
        $commandTester->execute(['configFile' => $configFile]);

        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Processing 2 schema compatibility configurations...', $output);
        self::assertStringContainsString(
            'Setting compatibility mode for schema "schema1" to "BACKWARD"... SUCCESS',
            $output
        );
        self::assertStringContainsString(
            'Setting compatibility mode for schema "schema2" to "FORWARD"... SUCCESS',
            $output
        );
        self::assertStringContainsString('Total schemas processed: 2', $output);
        self::assertStringContainsString('Successful updates: 2', $output);
        self::assertStringContainsString('Failed updates: 0', $output);
        self::assertEquals(0, $commandTester->getStatusCode());

        unlink($configFile);
    }

    public function testCommandWithValidConfigFileAndSomeFailures(): void
    {
        $configFile = $this->createTempConfigFile([
            ['schemaName' => 'schema1', 'compatibilityLevel' => 'BACKWARD'],
            ['schemaName' => 'schema2', 'compatibilityLevel' => 'FORWARD'],
            ['schemaName' => 'schema3', 'compatibilityLevel' => 'FULL'],
        ]);

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class);
        $schemaRegistryApi->method('setSubjectCompatibilityLevel')
            ->willReturnOnConsecutiveCalls(true, false, true);

        $commandTester = $this->createCommandTester($schemaRegistryApi);
        $commandTester->execute(['configFile' => $configFile]);

        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Processing 3 schema compatibility configurations...', $output);
        self::assertStringContainsString(
            'Setting compatibility mode for schema "schema1" to "BACKWARD"... SUCCESS',
            $output
        );
        self::assertStringContainsString(
            'Setting compatibility mode for schema "schema2" to "FORWARD"... FAILED',
            $output
        );
        self::assertStringContainsString(
            'Setting compatibility mode for schema "schema3" to "FULL"... SUCCESS',
            $output
        );
        self::assertStringContainsString('Total schemas processed: 3', $output);
        self::assertStringContainsString('Successful updates: 2', $output);
        self::assertStringContainsString('Failed updates: 1', $output);
        self::assertEquals(1, $commandTester->getStatusCode());

        unlink($configFile);
    }

    public function testCommandWithApiException(): void
    {
        $configFile = $this->createTempConfigFile([
            ['schemaName' => 'schema1', 'compatibilityLevel' => 'BACKWARD'],
            ['schemaName' => 'schema2', 'compatibilityLevel' => 'FORWARD'],
        ]);

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class);
        $schemaRegistryApi->method('setSubjectCompatibilityLevel')
            ->willReturnOnConsecutiveCalls(
                true,
                $this->throwException(new \Exception('API Error: Schema not found'))
            );

        $commandTester = $this->createCommandTester($schemaRegistryApi);
        $commandTester->execute(['configFile' => $configFile]);

        $output = $commandTester->getDisplay();

        self::assertStringContainsString(
            'Setting compatibility mode for schema "schema1" to "BACKWARD"... SUCCESS',
            $output
        );
        self::assertStringContainsString(
            'Setting compatibility mode for schema "schema2" to "FORWARD"... FAILED: API Error: Schema not found',
            $output
        );
        self::assertStringContainsString('Successful updates: 1', $output);
        self::assertStringContainsString('Failed updates: 1', $output);
        self::assertEquals(1, $commandTester->getStatusCode());

        unlink($configFile);
    }

    public function testCommandWithNonExistentConfigFile(): void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class);

        $commandTester = $this->createCommandTester($schemaRegistryApi);
        $commandTester->execute(['configFile' => '/non/existent/file.json']);

        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Could not read configuration file: /non/existent/file.json', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    /**
     * @dataProvider invalidJsonConfigurationProvider
     */
    public function testCommandWithInvalidJsonConfiguration(mixed $configData): void
    {
        if (is_string($configData)) {
            $configFile = tempnam(sys_get_temp_dir(), 'test_config');
            file_put_contents($configFile, $configData);

            /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
            $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class);

            $commandTester = $this->createCommandTester($schemaRegistryApi);
            $commandTester->execute(['configFile' => $configFile]);

            $output = $commandTester->getDisplay();

            self::assertStringContainsString(
                'Configuration file must contain a JSON array of schema configurations',
                $output
            );
            self::assertEquals(1, $commandTester->getStatusCode());

            unlink($configFile);

            return;
        }

        $configFile = $this->createTempConfigFile($configData);

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class);

        $commandTester = $this->createCommandTester($schemaRegistryApi);
        $commandTester->execute(['configFile' => $configFile]);

        $output = $commandTester->getDisplay();

        self::assertStringContainsString(
            'Configuration file must contain a JSON array of schema configurations',
            $output
        );
        self::assertEquals(1, $commandTester->getStatusCode());

        unlink($configFile);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidJsonConfigurationProvider(): array
    {
        return [
            'invalidJsonConfigurationProvider:1' => ['[invalid json}'],
            'invalidJsonConfigurationProvider:2' => [''],
            'invalidJsonConfigurationProvider:3' => [['invalid' => 'structure']],
            'invalidJsonConfigurationProvider:4' => ['not_an_array'],
            'invalidJsonConfigurationProvider:5' => [null],
            'invalidJsonConfigurationProvider:6' => [true],
            'invalidJsonConfigurationProvider:7' => [123],
        ];
    }

    public function testCommandWithMissingSchemaNameField(): void
    {
        $configFile = $this->createTempConfigFile([
            ['compatibilityLevel' => 'BACKWARD'],
            ['schemaName' => 'valid-schema', 'compatibilityLevel' => 'FORWARD'],
        ]);

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'setSubjectCompatibilityLevel' => true,
        ]);

        $commandTester = $this->createCommandTester($schemaRegistryApi);
        $commandTester->execute(['configFile' => $configFile]);

        $output = $commandTester->getDisplay();

        self::assertStringContainsString(
            'Invalid configuration at index 0: missing schemaName or compatibilityLevel',
            $output
        );
        self::assertStringContainsString(
            'Setting compatibility mode for schema "valid-schema" to "FORWARD"... SUCCESS',
            $output
        );
        self::assertStringContainsString('Successful updates: 1', $output);
        self::assertStringContainsString('Failed updates: 1', $output);
        self::assertEquals(1, $commandTester->getStatusCode());

        unlink($configFile);
    }

    public function testCommandWithMissingCompatibilityLevelField(): void
    {
        $configFile = $this->createTempConfigFile([
            ['schemaName' => 'incomplete-schema'],
            ['schemaName' => 'valid-schema', 'compatibilityLevel' => 'BACKWARD'],
        ]);

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'setSubjectCompatibilityLevel' => true,
        ]);

        $commandTester = $this->createCommandTester($schemaRegistryApi);
        $commandTester->execute(['configFile' => $configFile]);

        $output = $commandTester->getDisplay();

        self::assertStringContainsString(
            'Invalid configuration at index 0: missing schemaName or compatibilityLevel',
            $output
        );
        self::assertStringContainsString(
            'Setting compatibility mode for schema "valid-schema" to "BACKWARD"... SUCCESS',
            $output
        );
        self::assertStringContainsString('Successful updates: 1', $output);
        self::assertStringContainsString('Failed updates: 1', $output);
        self::assertEquals(1, $commandTester->getStatusCode());

        unlink($configFile);
    }

    public function testCommandWithEmptyArray(): void
    {
        $configFile = $this->createTempConfigFile([]);

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class);

        $commandTester = $this->createCommandTester($schemaRegistryApi);
        $commandTester->execute(['configFile' => $configFile]);

        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Processing 0 schema compatibility configurations...', $output);
        self::assertStringContainsString('Total schemas processed: 0', $output);
        self::assertStringContainsString('Successful updates: 0', $output);
        self::assertStringContainsString('Failed updates: 0', $output);
        self::assertEquals(0, $commandTester->getStatusCode());

        unlink($configFile);
    }

    public function testCommandWithAllValidCompatibilityLevels(): void
    {
        $compatibilityLevels = [
            'NONE', 'BACKWARD', 'BACKWARD_TRANSITIVE',
            'FORWARD', 'FORWARD_TRANSITIVE', 'FULL', 'FULL_TRANSITIVE',
        ];

        $schemas = [];
        foreach ($compatibilityLevels as $level) {
            $schemas[] = ['schemaName' => "schema-{$level}", 'compatibilityLevel' => $level];
        }

        $configFile = $this->createTempConfigFile($schemas);

        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'setSubjectCompatibilityLevel' => true,
        ]);

        $commandTester = $this->createCommandTester($schemaRegistryApi);
        $commandTester->execute(['configFile' => $configFile]);

        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Processing 7 schema compatibility configurations...', $output);

        foreach ($compatibilityLevels as $level) {
            self::assertStringContainsString(
                "Setting compatibility mode for schema \"schema-{$level}\" to \"{$level}\"... SUCCESS",
                $output
            );
        }

        self::assertStringContainsString('Successful updates: 7', $output);
        self::assertStringContainsString('Failed updates: 0', $output);
        self::assertEquals(0, $commandTester->getStatusCode());

        unlink($configFile);
    }

    private function createCommandTester(MockObject $schemaRegistryApi): CommandTester
    {
        $application = new Application();
        $application->add(new SetAllSchemasCompatibilityModeCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:set:compatibility:mode:all');

        return new CommandTester($command);
    }

    private function createTempConfigFile(mixed $config): string
    {
        $configFile = tempnam(sys_get_temp_dir(), 'test_config');
        file_put_contents($configFile, json_encode($config));

        return $configFile;
    }
}
