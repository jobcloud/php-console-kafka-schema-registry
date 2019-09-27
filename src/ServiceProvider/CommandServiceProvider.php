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
        $container['kafka_schema_commands'] = static function ($container) {

            if (false === isset($container['kafka.schema.registry.url'])) {
                throw new RuntimeException('Missing setting kafka.schema.registry.url in your container');
            }

            $auth = null;

            if (true === isset($container['kafka.schema.registry.auth'])) {
                $auth = $container['kafka.schema.registry.auth'];
            }

            $retries = 10;

            if (true === isset($container['kafka.schema.registry.retries'])) {
                $retries = $container['kafka.schema.registry.retries'];
            }

            return [
                new CheckCompatibilityCommand($container['kafka.schema.registry.url'], $auth),
                new CheckIsRegistredCommand($container['kafka.schema.registry.url'], $auth),
                new DeleteAllSchemasCommand($container['kafka.schema.registry.url'], $auth),
                new GetCompatibilityModeCommand($container['kafka.schema.registry.url'], $auth),
                new GetCompatibilityModeForSchemaCommand($container['kafka.schema.registry.url'], $auth),
                new GetLatestSchemaCommand($container['kafka.schema.registry.url'], $auth),
                new GetSchemaByVersionCommand($container['kafka.schema.registry.url'], $auth),
                new ListAllSchemasCommand($container['kafka.schema.registry.url'], $auth),
                new ListVersionsForSchemaCommand($container['kafka.schema.registry.url'], $auth),
                new RegisterChangedSchemasCommand($container['kafka.schema.registry.url'], $retries, $auth),
                new RegisterSchemaVersionCommand($container['kafka.schema.registry.url'], $auth),
            ];
        };
    }
}
