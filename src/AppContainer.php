<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole;

use Pimple\Container;

final class AppContainer
{

    /**
     * @param string $env
     * @return Container
     */
    public static function init(string $env): Container
    {
        $container = new Container(['env' => $env]);


        // Own
        $container
            ->register(new SchemaRegistryServiceProvider())
            ->register(new CommandServiceProvider());

        return $container;
    }
}
