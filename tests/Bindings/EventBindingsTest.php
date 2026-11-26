<?php

declare(strict_types=1);

use Alama\Arazzo\Contracts\Support\Events\Dispatcher\SimpleEventDispatcher;
use Alama\Arazzo\Laravel\Bindings\EventBindings;
use Psr\EventDispatcher\EventDispatcherInterface;

it('binds the psr-14 alias to the package dispatcher singleton', function (): void {
    expect(app(EventDispatcherInterface::class))->toBeInstanceOf(SimpleEventDispatcher::class)
        ->and(app(EventDispatcherInterface::class))->toBe(app(SimpleEventDispatcher::class));
});

it('returns a dispatcher on re-registration', function (): void {
    EventBindings::register($this->app);
    expect(app(EventDispatcherInterface::class))->toBeInstanceOf(SimpleEventDispatcher::class);
});
