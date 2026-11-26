<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Bindings;

use Alama\Arazzo\Contracts\Interfaces\LockManagerInterface;
use Alama\Arazzo\Contracts\Interfaces\QueueDriverInterface;
use Alama\Arazzo\Runner\AsyncRunner;
use Alama\Arazzo\Runner\DefinitionStoreInterface;
use Alama\Arazzo\Runner\ExecutionStoreInterface;
use Alama\Arazzo\Runner\Runner;
use Illuminate\Contracts\Container\Container;

/**
 * Execution pipeline: one AsyncRunner for durable/async execution and one
 * Runner for synchronous execution. Both are re-exported as container
 * singletons; hosts re-bind withStore/withQueueDriver/withLockManager via
 * the immutable with*() builders when durable ports are available.
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
final class ExecutionBindings
{
    public static function register(Container $app): void
    {
        $app->singleton(AsyncRunner::class, function (Container $app): AsyncRunner {
            return AsyncRunner::create()
                ->withStore($app->make(ExecutionStoreInterface::class))
                ->withDefinitions($app->make(DefinitionStoreInterface::class))
                ->withQueueDriver($app->make(QueueDriverInterface::class))
                ->withLockManager($app->make(LockManagerInterface::class));
        });

        $app->singleton(Runner::class, static fn (Container $app): Runner => Runner::create());
    }
}
