<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Bindings;

use Alama\Arazzo\Contracts\Support\Events\Dispatcher\SimpleEventDispatcher;
use Illuminate\Contracts\Container\Container;
use Psr\EventDispatcher\EventDispatcherInterface;

/** PSR-14 event bus: in-memory dispatcher.
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
final class EventBindings
{
    public static function register(Container $app): void
    {
        $app->singleton(SimpleEventDispatcher::class, static fn (): SimpleEventDispatcher => new SimpleEventDispatcher);

        $app->bindIf(
            EventDispatcherInterface::class,
            fn (Container $app) => $app->make(SimpleEventDispatcher::class),
        );
    }
}
