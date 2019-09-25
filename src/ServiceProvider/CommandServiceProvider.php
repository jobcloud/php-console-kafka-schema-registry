<?php

namespace Jobcloud\SchemaConsole\ServiceProvider;

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
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use \RuntimeException;

class CommandServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $container
     */
    public function register(Container $container)
    {
        if (false === isset($container['kafka.schema.registry.url'])) {
            throw new RuntimeException('Missing setting kafka.schema.registry.url in your container');
        }

        $container['kafka_schema_commands'] = static function ($container) {
            return [
                new CheckCompatibilityCommand($container['kafka.schema.registry.url']),
                new CheckIsRegistredCommand($container['kafka.schema.registry.url']),
                new DeleteAllSchemasCommand($container['kafka.schema.registry.url']),
                new GetCompatibilityModeCommand($container['kafka.schema.registry.url']),
                new GetCompatibilityModeForSchemaCommand($container['kafka.schema.registry.url']),
                new GetLatestSchemaCommand($container['kafka.schema.registry.url']),
                new GetSchemaByVersionCommand($container['kafka.schema.registry.url']),
                new ListAllSchemasCommand($container['kafka.schema.registry.url']),
                new ListVersionsForSchemaCommand($container['kafka.schema.registry.url']),
                new RegisterChangedSchemasCommand($container['kafka.schema.registry.url']),
                new RegisterSchemaVersionCommand($container['kafka.schema.registry.url']),
            ];
        };
    }
}
