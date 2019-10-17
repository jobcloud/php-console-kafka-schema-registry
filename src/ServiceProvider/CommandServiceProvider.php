<?php

namespace Jobcloud\SchemaConsole\ServiceProvider;

use Jobcloud\SchemaConsole\Command\CheckAllSchemasHaveValidStructureCommand;
use Jobcloud\SchemaConsole\Command\CheckAllSchemasCompatibilityCommand;
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
use Jobcloud\SchemaConsole\SchemaRegistryApi;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use GuzzleHttp\Client;
use RuntimeException;

class CommandServiceProvider implements ServiceProviderInterface
{

    public const COMMANDS = 'kafka.schema.registry.commands';
    public const CLIENT = 'kafka.schema.registry.client';
    public const REGISTRY_URL = 'kafka.schema.registry.url';
    public const USERNAME = 'kafka.schema.registry.username';
    public const PASSWORD = 'kafka.schema.registry.password';

    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container)
    {
        $container[self::COMMANDS] = static function (Container $container) {

            if (!$container->offsetExists(self::CLIENT)) {
                if (!$container->offsetExists(self::REGISTRY_URL)) {
                    throw new RuntimeException(
                        sprintf("Missing setting '%s' in your container", self::REGISTRY_URL)
                    );
                }

                $clientConfig = ['base_uri' => $container[self::REGISTRY_URL]];

                if ($container->offsetExists(self::USERNAME) && $container->offsetExists(self::PASSWORD)) {
                    $clientConfig['auth'] = [$container[self::USERNAME], $container[self::PASSWORD]];
                }

                $container[self::CLIENT] = new Client($clientConfig);
            }

            $schemaRegistryApi = new SchemaRegistryApi($container[self::CLIENT]);

            return [
                new CheckCompatibilityCommand($schemaRegistryApi),
                new CheckIsRegistredCommand($schemaRegistryApi),
                new DeleteAllSchemasCommand($schemaRegistryApi),
                new GetCompatibilityModeCommand($schemaRegistryApi),
                new CheckAllSchemasCompatibilityCommand($schemaRegistryApi),
                new GetCompatibilityModeForSchemaCommand($schemaRegistryApi),
                new GetLatestSchemaCommand($schemaRegistryApi),
                new GetSchemaByVersionCommand($schemaRegistryApi),
                new ListAllSchemasCommand($schemaRegistryApi),
                new ListVersionsForSchemaCommand($schemaRegistryApi),
                new RegisterChangedSchemasCommand($schemaRegistryApi),
                new RegisterSchemaVersionCommand($schemaRegistryApi),
                new CheckAllSchemasHaveValidStructureCommand(),
            ];
        };
    }
}
