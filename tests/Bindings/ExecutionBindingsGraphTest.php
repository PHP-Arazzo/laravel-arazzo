<?php

declare(strict_types=1);

use Alama\Arazzo\Runner\AsyncRunner;

it('registers the AsyncRunner binding and no leaked node aliases', function (): void {
    $bindings = app()->getBindings();
    expect(array_key_exists(AsyncRunner::class, $bindings))->toBeTrue();

    foreach ([
        'Alama\Arazzo\Runner\Sync\StepExecutor',
        'Alama\Arazzo\Runner\Async\StepOutcomeHandler',
        'Alama\Arazzo\Runner\Async\CorrelationResumer',
        'Alama\Arazzo\Runner\Async\StepExecutionWorker',
    ] as $removedAlias) {
        expect(array_key_exists($removedAlias, $bindings))->toBeFalse();
    }
});

it('AsyncRunner resolves from container with durable ports wired', function (): void {
    $runner = app(AsyncRunner::class);

    expect($runner)->toBeInstanceOf(AsyncRunner::class);
});
