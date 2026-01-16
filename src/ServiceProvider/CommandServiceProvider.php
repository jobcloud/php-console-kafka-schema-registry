<?php

namespace Jobcloud\SchemaConsole\ServiceProvider;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Jobcloud\Kafka\SchemaRegistryClient\ServiceProvider\KafkaSchemaRegistryApiClientProvider;
use Jobcloud\SchemaConsole\Command\CheckAllSchemaTemplatesNamesCommand;
use Jobcloud\SchemaConsole\Command\CheckAllSchemasAreValidAvroCommand;
use Jobcloud\SchemaConsole\Command\CheckAllSchemasCompatibilityCommand;
use Jobcloud\SchemaConsole\Command\CheckAllSchemaTemplatesDefaultTypeCommand;
use Jobcloud\SchemaConsole\Command\CheckAllSchemaTemplatesDocCommentsCommand;
use Jobcloud\SchemaConsole\Command\CheckCompatibilityCommand;
use Jobcloud\SchemaConsole\Command\CheckDocCommentsCommand;
use Jobcloud\SchemaConsole\Command\CheckIsRegisteredCommand;
use Jobcloud\SchemaConsole\Command\DeleteAllSchemasCommand;
use Jobcloud\SchemaConsole\Command\GetCompatibilityModeCommand;
use Jobcloud\SchemaConsole\Command\GetCompatibilityModeForSchemaCommand;
use Jobcloud\SchemaConsole\Command\GetLatestSchemaCommand;
use Jobcloud\SchemaConsole\Command\GetSchemaByVersionCommand;
use Jobcloud\SchemaConsole\Command\ListAllSchemasCommand;
use Jobcloud\SchemaConsole\Command\ListVersionsForSchemaCommand;
use Jobcloud\SchemaConsole\Command\RegisterChangedSchemasCommand;
use Jobcloud\SchemaConsole\Command\RegisterSchemaVersionCommand;
use Jobcloud\SchemaConsole\Command\SetAllSchemasCompatibilityModeCommand;
use Jobcloud\SchemaConsole\Command\SetCompatibilityModeForSchemaCommand;
use Jobcloud\SchemaConsole\Command\SetImportModeCommand;
use Jobcloud\SchemaConsole\Command\SetReadOnlyModeCommand;
use Jobcloud\SchemaConsole\Command\SetReadWriteModeCommand;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CommandServiceProvider implements ServiceProviderInterface
{
    public const string COMMANDS = 'kafka.schema.registry.commands';

    #[\Override]
    public function register(Container $pimple): void
    {
        $pimple->register(new KafkaSchemaRegistryApiClientProvider());

        $pimple[self::COMMANDS] = static function (Container $pimple): array {

            /** @var KafkaSchemaRegistryApiClientInterface $schemaRegistryApi */
            $schemaRegistryApi = $pimple[KafkaSchemaRegistryApiClientProvider::API_CLIENT];

            return [
                new CheckCompatibilityCommand($schemaRegistryApi),
                new CheckIsRegisteredCommand($schemaRegistryApi),
                new DeleteAllSchemasCommand($schemaRegistryApi),
                new GetCompatibilityModeCommand($schemaRegistryApi),
                new CheckAllSchemasCompatibilityCommand($schemaRegistryApi),
                new GetCompatibilityModeForSchemaCommand($schemaRegistryApi),
                new SetAllSchemasCompatibilityModeCommand($schemaRegistryApi),
                new SetCompatibilityModeForSchemaCommand($schemaRegistryApi),
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
                new CheckAllSchemaTemplatesDocCommentsCommand(),
                new CheckAllSchemaTemplatesNamesCommand(),
            ];
        };
    }
}
