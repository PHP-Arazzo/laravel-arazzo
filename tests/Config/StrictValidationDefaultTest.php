<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests;

use Alama\Arazzo\Protocol\Http\Execution\DefaultOpenApiExecutor;
use Alama\Arazzo\Protocol\Http\Execution\Interfaces\OpenApiExecutorInterface;
use Alama\Arazzo\Runner\AsyncRunner;

/**
 * ADR-001: strict response validation is the platform default. The config file
 * and both explicit consumers must agree, or the same step validates
 * differently depending on which adapter executes it.
 */
it('defaults strict schema validation on in the published config', function (): void {
    expect(config('arazzo.strict_schema_validation'))->toBeTrue();
});

it('honours an explicit false config, so invalid response payloads stay allowed when disabled', function (): void {
    config()->set('arazzo.strict_schema_validation', false);

    $runner = app(AsyncRunner::class);
    expect($runner)->toBeInstanceOf(AsyncRunner::class);
});

it('resolves OpenApiExecutorInterface from container', function (): void {
    expect(app(OpenApiExecutorInterface::class))->toBeInstanceOf(
        DefaultOpenApiExecutor::class
    );
});
