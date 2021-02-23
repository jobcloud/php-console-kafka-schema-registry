<?php

namespace Jobcloud\SchemaConsole\Tests\ServiceProvider;

use Jobcloud\Kafka\SchemaRegistryClient\ServiceProvider\KafkaSchemaRegistryApiClientProvider;
use Jobcloud\SchemaConsole\Command\CheckAllSchemasCompatibilityCommand;
use Jobcloud\SchemaConsole\Command\CheckAllSchemasAreValidAvroCommand;
use Jobcloud\SchemaConsole\Command\CheckAllSchemaTemplatesDocCommentsCommand;
use Jobcloud\SchemaConsole\Command\CheckCompatibilityCommand;
use Jobcloud\SchemaConsole\Command\CheckDocCommentsCommand;
use Jobcloud\SchemaConsole\Command\CheckIsRegistredCommand;
use Jobcloud\SchemaConsole\Command\DeleteAllSchemasCommand;
use Jobcloud\SchemaConsole\Command\GetCompatibilityModeCommand;
use Jobcloud\SchemaConsole\Command\GetCompatibilityModeForSchemaCommand;
use Jobcloud\SchemaConsole\Command\GetLatestSchemaCommand;
use Jobcloud\SchemaConsole\Command\GetSchemaByVersionCommand;
use Jobcloud\SchemaConsole\Command\ListAllSchemasCommand;
use Jobcloud\SchemaConsole\Command\ListVersionsForSchemaCommand;
use Jobcloud\SchemaConsole\Command\RegisterChangedSchemasCommand;
use Jobcloud\SchemaConsole\Command\RegisterSchemaVersionCommand;
use Jobcloud\SchemaConsole\Command\SetImportModeCommand;
use Jobcloud\SchemaConsole\Command\SetReadOnlyModeCommand;
use Jobcloud\SchemaConsole\Command\SetReadWriteModeCommand;
use Jobcloud\SchemaConsole\ServiceProvider\CommandServiceProvider;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;

/**
 * @covers \Jobcloud\SchemaConsole\ServiceProvider\CommandServiceProvider
 */
class CommandServiceProviderTest extends AbstractSchemaRegistryTestCase
{
    public function testMakesServicesInContainer():void
    {
        $container = new Container();

        self::assertArrayNotHasKey(CommandServiceProvider::COMMANDS, $container);

        $container[KafkaSchemaRegistryApiClientProvider::CONTAINER_KEY] = [
            KafkaSchemaRegistryApiClientProvider::SETTING_KEY_BASE_URL => 'http://registry-url'
        ];

        $container->register(new CommandServiceProvider());

        $container[CommandServiceProvider::COMMANDS]; // We instantiate service creation by this

        self::assertArrayHasKey(CommandServiceProvider::COMMANDS, $container);
    }

    public function testAllCommandsThereAreInstancesOfCommand():void
    {
        $container = new Container();

        $container[KafkaSchemaRegistryApiClientProvider::CONTAINER_KEY] = [
            KafkaSchemaRegistryApiClientProvider::SETTING_KEY_BASE_URL => 'http://registry-url'
        ];

        $container->register(new CommandServiceProvider());
        $commands = $container[CommandServiceProvider::COMMANDS];

        foreach ($commands as $command){
            self::assertInstanceOf(Command::class, $command);
        }
    }

    public function testHasAllOfTheCommands():void
    {
        $container = new Container();

        $container[KafkaSchemaRegistryApiClientProvider::CONTAINER_KEY] = [
            KafkaSchemaRegistryApiClientProvider::SETTING_KEY_BASE_URL => 'http://registry-url'
        ];

        $container->register(new CommandServiceProvider());
        $commands = $container[CommandServiceProvider::COMMANDS];

        self::assertArrayHasInstanceOf(CheckCompatibilityCommand::class, $commands);
        self::assertArrayHasInstanceOf(CheckAllSchemasCompatibilityCommand::class, $commands);
        self::assertArrayHasInstanceOf(CheckIsRegistredCommand::class, $commands);
        self::assertArrayHasInstanceOf(DeleteAllSchemasCommand::class, $commands);
        self::assertArrayHasInstanceOf(GetCompatibilityModeCommand::class, $commands);
        self::assertArrayHasInstanceOf(GetCompatibilityModeForSchemaCommand::class, $commands);
        self::assertArrayHasInstanceOf(GetLatestSchemaCommand::class, $commands);
        self::assertArrayHasInstanceOf(GetSchemaByVersionCommand::class, $commands);
        self::assertArrayHasInstanceOf(ListAllSchemasCommand::class, $commands);
        self::assertArrayHasInstanceOf(ListVersionsForSchemaCommand::class, $commands);
        self::assertArrayHasInstanceOf(RegisterChangedSchemasCommand::class, $commands);
        self::assertArrayHasInstanceOf(RegisterSchemaVersionCommand::class, $commands);
        self::assertArrayHasInstanceOf(CheckAllSchemasAreValidAvroCommand::class, $commands);
        self::assertArrayHasInstanceOf(SetImportModeCommand::class, $commands);
        self::assertArrayHasInstanceOf(SetReadOnlyModeCommand::class, $commands);
        self::assertArrayHasInstanceOf(SetReadWriteModeCommand::class, $commands);
        self::assertArrayHasInstanceOf(CheckDocCommentsCommand::class, $commands);
        self::assertArrayHasInstanceOf(CheckAllSchemaTemplatesDocCommentsCommand::class, $commands);
    }
}
