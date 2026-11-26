<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests\Support;

use Alama\Arazzo\Laravel\Support\Config;

it('definitionsTable returns configured table name', function (): void {
    config(['arazzo.definitions_table' => 'custom_definitions']);
    expect(Config::definitionsTable())->toBe('custom_definitions');
});

it('definitionsTable returns default when not configured', function (): void {
    config(['arazzo.definitions_table' => null]);
    expect(Config::definitionsTable())->toBe('arazzo_definitions');
});

it('executionsTable returns configured table name', function (): void {
    config(['arazzo.executions_table' => 'custom_executions']);
    expect(Config::executionsTable())->toBe('custom_executions');
});

it('eventsTable returns configured table name', function (): void {
    config(['arazzo.events_table' => 'custom_events']);
    expect(Config::eventsTable())->toBe('custom_events');
});

it('pendingCorrelationsTable returns configured table name', function (): void {
    config(['arazzo.pending_correlations_table' => 'custom_correlations']);
    expect(Config::pendingCorrelationsTable())->toBe('custom_correlations');
});

it('stateTtlSeconds returns configured TTL', function (): void {
    config(['arazzo.state_ttl' => 3600]);
    expect(Config::stateTtlSeconds())->toBe(3600);
});

it('stateTtlSeconds returns default when not configured', function (): void {
    config(['arazzo.state_ttl' => null]);
    expect(Config::stateTtlSeconds())->toBe(86400);
});
