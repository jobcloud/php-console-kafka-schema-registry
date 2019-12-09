<?php

namespace Jobcloud\SchemaConsole\ServiceProvider;

use Jobcloud\KafkaSchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Jobcloud\KafkaSchemaRegistryClient\ServiceProvider\KafkaSchemaRegistryApiClientProvider;
use Jobcloud\SchemaConsole\Command\CheckAllSchemasAreValidAvroCommand;
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
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CommandServiceProvider implements ServiceProviderInterface
{

    public const COMMANDS = 'kafka.schema.registry.commands';

    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container)
    {
        $container->register(new KafkaSchemaRegistryApiClientProvider());

        $container[self::COMMANDS] = static function (Container $container) {

            /** @var KafkaSchemaRegistryApiClientInterface $schemaRegistryApi */
            $schemaRegistryApi = $container[KafkaSchemaRegistryApiClientProvider::API_CLIENT];

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
                new CheckAllSchemasAreValidAvroCommand(),
            ];
        };
    }
}
