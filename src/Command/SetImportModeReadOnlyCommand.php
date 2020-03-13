<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;

class SetImportModeReadOnlyCommand extends AbstractImportCommand
{
    /**
     * @inheritDoc
     */
    public function getMode(): string
    {
        return KafkaSchemaRegistryApiClientInterface::MODE_READONLY;
    }
}
