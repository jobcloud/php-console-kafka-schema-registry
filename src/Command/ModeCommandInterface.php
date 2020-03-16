<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

/**
 * @package Jobcloud\SchemaConsole\Command
 */
interface ModeCommandInterface
{
    /**
     * Gets name of Import Mode
     *
     * @return string
     */
    public function getMode(): string;
}
