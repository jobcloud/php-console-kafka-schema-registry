<?php


namespace Jobcloud\SchemaConsole;


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
        $application
            ->add(new RegisterSchemaVersionCommand($registryUrl))
            ;
    }
}