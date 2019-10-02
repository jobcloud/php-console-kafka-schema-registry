<?php

namespace Jobcloud\SchemaConsole\Tests;

use GuzzleHttp\Client;
use Jobcloud\SchemaConsole\Command\CheckCompatibilityCommand;
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
use Jobcloud\SchemaConsole\ServiceProvider\CommandServiceProvider;
use Pimple\Container;
use RuntimeException;
use Symfony\Component\Console\Command\Command;

class CommandServiceProviderTest extends AbstractSchemaRegistryTestCase
{
    public function testMakesServicesInContainer():void
    {
        $container = new Container();

        self::assertArrayNotHasKey(CommandServiceProvider::COMMANDS, $container);
        self::assertArrayNotHasKey(CommandServiceProvider::REGISTRY_URL, $container);
        self::assertArrayNotHasKey(CommandServiceProvider::PASSWORD, $container);
        self::assertArrayNotHasKey(CommandServiceProvider::USERNAME, $container);
        self::assertArrayNotHasKey(CommandServiceProvider::CLIENT, $container);

        $container[CommandServiceProvider::REGISTRY_URL] = 'http://registry-url';

        $container->register(new CommandServiceProvider());

        $container[CommandServiceProvider::COMMANDS]; // We instantiate service creation by this

        self::assertArrayHasKey(CommandServiceProvider::COMMANDS, $container);
        self::assertArrayHasKey(CommandServiceProvider::CLIENT, $container);

        self::assertArrayNotHasKey(CommandServiceProvider::PASSWORD, $container);
        self::assertArrayNotHasKey(CommandServiceProvider::USERNAME, $container);
    }

    public function testThrowExceptionOnMissingRegistryUrl():void
    {
        $container = new Container();

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage(
            sprintf("Missing setting '%s' in your container", CommandServiceProvider::REGISTRY_URL)
        );

        $container->register(new CommandServiceProvider());
        $container[CommandServiceProvider::COMMANDS];
    }

    public function testPuttingUsernameAndPasswordIntoClient():void
    {
        $container = new Container();

        $container[CommandServiceProvider::USERNAME] = 'username';
        $container[CommandServiceProvider::PASSWORD] = 'password';
        $container[CommandServiceProvider::REGISTRY_URL] = 'http://registry-url';

        $container->register(new CommandServiceProvider());
        $container[CommandServiceProvider::COMMANDS];

        /** @var Client $client */
        $client = $container[CommandServiceProvider::CLIENT];

        self::assertEquals(['username', 'password'], $client->getConfig('auth'));
    }

    public function testAllCommandsThereAreInstancesOfCommand():void
    {
        $container = new Container();

        $container[CommandServiceProvider::REGISTRY_URL] = 'http://registry-url';

        $container->register(new CommandServiceProvider());
        $commands = $container[CommandServiceProvider::COMMANDS];

        foreach ($commands as $command){
            self::assertInstanceOf(Command::class, $command);
        }
    }

    public function testHasAllOfTheCommands():void
    {
        $container = new Container();

        $container[CommandServiceProvider::REGISTRY_URL] = 'http://registry-url';

        $container->register(new CommandServiceProvider());
        $commands = $container[CommandServiceProvider::COMMANDS];

        self::assertArrayHasInstanceOf(CheckCompatibilityCommand::class, $commands);
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
    }
}