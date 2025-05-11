<?php

namespace DuaneStorey\AiTools\Core;

/**
 * Version information for the AI Tools package
 */
class Version
{
    /**
     * The current version of the package
     */
    public const VERSION = '1.0.5';

    /**
     * Get the current version of the package
     */
    public static function get(): string
    {
        return self::VERSION;
    }
}
