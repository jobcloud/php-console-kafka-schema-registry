<?php

namespace Jobcloud\SchemaConsole\ServiceProvider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Jobcloud\Kafka\SchemaRegistryClient\ServiceProvider\KafkaSchemaRegistryApiClientProvider;
use Jobcloud\SchemaConsole\Command\CheckAllSchemasAreValidAvroCommand;
use Jobcloud\SchemaConsole\Command\CheckAllSchemasCompatibilityCommand;
use Jobcloud\SchemaConsole\Command\CheckAllSchemaTemplatesDefaultTypeCommand;
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
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Client\ClientInterface;

class CommandServiceProvider implements ServiceProviderInterface
{
    public const COMMANDS = 'kafka.schema.registry.commands';

    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container)
    {
        $container[KafkaSchemaRegistryApiClientProvider::REQUEST_FACTORY] = static function (): HttpFactory {
            return new HttpFactory();
        };

        $container[KafkaSchemaRegistryApiClientProvider::CLIENT] = static function (): ClientInterface {
            return new Client();
        };

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
                new SetImportModeCommand($schemaRegistryApi),
                new SetReadOnlyModeCommand($schemaRegistryApi),
                new SetReadWriteModeCommand($schemaRegistryApi),
                new CheckAllSchemasAreValidAvroCommand(),
                new CheckAllSchemaTemplatesDefaultTypeCommand(),
                new CheckDocCommentsCommand(),
                new CheckAllSchemaTemplatesDocCommentsCommand()
            ];
        };
    }
}
