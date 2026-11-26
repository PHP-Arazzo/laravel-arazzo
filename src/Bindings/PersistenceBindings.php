<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Bindings;

use Alama\Arazzo\Contracts\Interfaces\LockManagerInterface;
use Alama\Arazzo\Contracts\Interfaces\LockStrategyInterface;
use Alama\Arazzo\Contracts\Interfaces\QueueDriverInterface;
use Alama\Arazzo\Laravel\Lock\LaravelRedisLockManager;
use Alama\Arazzo\Laravel\Persistence\DatabaseDefinitionStore;
use Alama\Arazzo\Laravel\Persistence\DatabaseExecutionStore;
use Alama\Arazzo\Laravel\Queue\LaravelQueueDriver;
use Alama\Arazzo\Laravel\Support\Config;
use Alama\Arazzo\Runner\DefinitionStoreInterface;
use Alama\Arazzo\Runner\ExecutionStoreInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;

/**
 * Durable-execution persistence ports: consolidated execution store (state +
 * execution lifecycle + pending correlations) and definition store, plus the
 * queue/lock transports.
 *
 * Every binding is a singleton registration of a concrete implementation.
 * The container auto-wires all constructor dependencies (RedisFactory,
 * ConnectionInterface, Parser, Config, etc.) — no manual `new`, no
 * `$app->make()`, no `config()` helper in bindings.
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
final class PersistenceBindings
{
    public static function register(Container $app): void
    {
        $app->singleton(Config::class);

        // Resolve the default DB connection as ConnectionInterface so it can
        // be auto-wired into store constructors.
        $app->singleton(ConnectionInterface::class, function (Container $app) {
            $dbManager = $app->make('db');

            return $dbManager->connection();
        });

        // Consolidated execution store: hot state + lifecycle + correlations.
        $app->singleton(ExecutionStoreInterface::class, DatabaseExecutionStore::class);

        // Definition store: wraps DatabaseDefinitionRegistry.
        $app->singleton(DefinitionStoreInterface::class, DatabaseDefinitionStore::class);

        // Queue/lock transports.
        $app->singleton(LockManagerInterface::class, LaravelRedisLockManager::class);
        // New code may inject the strategy name; same underlying singleton.
        $app->singleton(LockStrategyInterface::class, LockManagerInterface::class);
        $app->singleton(QueueDriverInterface::class, LaravelQueueDriver::class);
    }
}
