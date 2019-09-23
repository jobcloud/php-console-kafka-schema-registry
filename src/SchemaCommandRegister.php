<?php

namespace Jobcloud\SchemaConsole;

use Jobcloud\SchemaConsole\Command\CheckCompatibilityCommand;
use Jobcloud\SchemaConsole\Command\CheckIsRegistredCommand;
use Jobcloud\SchemaConsole\Command\GetSchemaByVersionCommand;
use Jobcloud\SchemaConsole\Command\GetLatestSchemaCommand;
use Jobcloud\SchemaConsole\Command\ListAllSchemasCommand;
use Jobcloud\SchemaConsole\Command\ListVersionsForSchemaCommand;
use Jobcloud\SchemaConsole\Command\RegisterChangedSchemasCommand;
use Jobcloud\SchemaConsole\Command\RegisterSchemaVersionCommand;
use Symfony\Component\Console\Application;

class SchemaCommandRegister
{
    /**
     * SchemaCommandRegister constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param Application $application
     * @param string $registryUrl
     */
    public static function register(Application $application, string $registryUrl): void {
        $application->addCommands([
            new RegisterSchemaVersionCommand($registryUrl),
            new ListAllSchemasCommand($registryUrl),
            new ListVersionsForSchemaCommand($registryUrl),
            new GetSchemaByVersionCommand($registryUrl),
            new CheckCompatibilityCommand($registryUrl),
            new CheckIsRegistredCommand($registryUrl),
            new GetLatestSchemaCommand($registryUrl),
            new RegisterChangedSchemasCommand($registryUrl)
        ]);
    }
}
