# php-console-kafka-schema-registry

You can register each command separately, but you can also register all by:
```php
use Jobcloud\SchemaConsole\SchemaCommandRegister;

SchemaCommandRegister::register(
    $application, // Your symfony console application
    'http://registry/' // Your Schema Registry URL path
);
```