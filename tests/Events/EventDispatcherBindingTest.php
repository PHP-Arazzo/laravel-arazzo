<?php

declare(strict_types=1);

use Alama\Arazzo\Contracts\Support\Events\Dispatcher\SimpleEventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;

it('binds EventDispatcherInterface to SimpleEventDispatcher by default', function () {
    expect(app(EventDispatcherInterface::class))->toBeInstanceOf(SimpleEventDispatcher::class);
});

it('resolves same SimpleEventDispatcher instance across calls (singleton)', function () {
    expect(app(SimpleEventDispatcher::class))->toBe(app(SimpleEventDispatcher::class));
});
