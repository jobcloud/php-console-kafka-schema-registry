# php-console-kafka-schema-registry

## Installation
```
composer require jobcloud/php-console-kafka-schema-registry "~1.0"
```

## Register commands
You can register each command separately like this:
```php
<?php

use Symfony\Component\Console\Application;
use Jobcloud\SchemaConsole\Command\ListAllSchemasCommand;
use Jobcloud\SchemaConsole\SchemaRegistryApi;
use GuzzleHttp\Client;

$client = new Client(
    [
        'base_uri' => 'url-to-your-schema-api',
        //'auth' => ['schema-username', 'schema-password']
    ]
);
$schemaRegistryApi = new SchemaRegistryApi($client);

$console = new Application();
$console->add(new ListAllSchemasCommand($schemaRegistryApi));
```
 
or you can also register them over the service provider:

```php
<?php

use Jobcloud\SchemaConsole\ServiceProvider\CommandServiceProvider;
use Pimple\Container;

$container = new Container();
$container->register(new CommandServiceProvider());
```

**Note:** To use the service provider you need to set the following in your container:
- either `kafka.schema.registry.client` which is the guzzle client or alternatively `kafka.schema.registry.url`
- if you need to use basic authentication you need to set `kafka.schema.registry.username` and `kafka.schema.registry.password`
