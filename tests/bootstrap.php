<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Tests;

use Composer\Autoload\ClassLoader;

/** @var ClassLoader $loader */
$loader = require __DIR__ . '/../vendor/autoload.php';

$loader->setPsr4('Jobcloud\SchemaConsole\Tests\\', __DIR__);

echo sprintf('PHP version: %s', PHP_VERSION) . PHP_EOL;
