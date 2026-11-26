<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Support;

/**
 * Configuration accessor for arazzo package settings.
 *
 * Follows spatie/laravel-permission's Config pattern: centralizes config
 * key access with sensible defaults, so models and bindings don't call
 * config() directly.
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
final class Config
{
    public static function definitionsTable(): string
    {
        $value = config('arazzo.definitions_table', 'arazzo_definitions');

        return is_string($value) ? $value : 'arazzo_definitions';
    }

    public static function executionsTable(): string
    {
        $value = config('arazzo.executions_table', 'arazzo_executions');

        return is_string($value) ? $value : 'arazzo_executions';
    }

    public static function eventsTable(): string
    {
        $value = config('arazzo.events_table', 'arazzo_events');

        return is_string($value) ? $value : 'arazzo_events';
    }

    public static function pendingCorrelationsTable(): string
    {
        $value = config('arazzo.pending_correlations_table', 'arazzo_pending_correlations');

        return is_string($value) ? $value : 'arazzo_pending_correlations';
    }

    public static function stateTtlSeconds(): int
    {
        $value = config('arazzo.state_ttl', 86400);

        return is_int($value) ? $value : 86400;
    }
}
