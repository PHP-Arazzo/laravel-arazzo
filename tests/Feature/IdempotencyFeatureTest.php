<?php

declare(strict_types=1);

namespace Tests\Feature;

use Alama\Arazzo\Laravel\Bindings\ExecutionBindings;
use Alama\Arazzo\Runner\AsyncRunner;

it('execution bindings register AsyncRunner when idempotency is enabled', function (): void {
    config(['arazzo.idempotency.enabled' => true]);

    ExecutionBindings::register($this->app);

    expect(app(AsyncRunner::class))->toBeInstanceOf(AsyncRunner::class);
});

it('execution bindings register AsyncRunner when idempotency is disabled', function (): void {
    config(['arazzo.idempotency.enabled' => false]);

    ExecutionBindings::register($this->app);

    expect(app(AsyncRunner::class))->toBeInstanceOf(AsyncRunner::class);
});
