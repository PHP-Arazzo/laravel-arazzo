<?php

declare(strict_types=1);

use Alama\Arazzo\Contracts\Interfaces\LockManagerInterface;
use Alama\Arazzo\Contracts\Interfaces\LockStrategyInterface;
use Alama\Arazzo\Contracts\Interfaces\PendingCorrelationRegistryInterface;
use Alama\Arazzo\Contracts\Interfaces\QueueDriverInterface;
use Alama\Arazzo\Laravel\Bindings\PersistenceBindings;
use Alama\Arazzo\Laravel\Lock\LaravelRedisLockManager;
use Alama\Arazzo\Laravel\Persistence\DatabaseDefinitionStore;
use Alama\Arazzo\Laravel\Persistence\DatabaseExecutionStore;
use Alama\Arazzo\Laravel\Queue\LaravelQueueDriver;
use Alama\Arazzo\Runner\DefinitionStoreInterface;
use Alama\Arazzo\Runner\ExecutionStoreInterface;

function bindingsProp(object $instance, string $name): mixed
{
    $prop = new ReflectionProperty($instance, $name);
    $prop->setAccessible(true);

    return $prop->getValue($instance);
}

it('binds each persistence port as a singleton', function (): void {
    foreach ([
        ExecutionStoreInterface::class,
        DefinitionStoreInterface::class,
        QueueDriverInterface::class,
    ] as $abstract) {
        expect(app($abstract))->toBe(app($abstract));
    }
});

it('aliases LockStrategyInterface onto the same lock-manager instance', function (): void {
    $manager = app(LockManagerInterface::class);
    $strategy = app(LockStrategyInterface::class);

    expect($manager)->toBeInstanceOf(LaravelRedisLockManager::class)
        ->and($strategy)->toBe($manager);
});

it('feeds state_ttl from config into the execution store', function (): void {
    config()->set('arazzo.state_ttl', 4321);
    app()->forgetInstance(ExecutionStoreInterface::class);

    $store = app(ExecutionStoreInterface::class);

    expect($store)->toBeInstanceOf(DatabaseExecutionStore::class);
});

it('feeds the pending-correlations table name from config', function (): void {
    config()->set('arazzo.pending_correlations_table', 'corr_v2');
    app()->forgetInstance(PendingCorrelationRegistryInterface::class);

    expect(app(QueueDriverInterface::class))->toBeInstanceOf(LaravelQueueDriver::class);
});

it('registers the persistence bindings on the container', function (): void {
    PersistenceBindings::register($this->app);

    expect(app(ExecutionStoreInterface::class))->toBeInstanceOf(DatabaseExecutionStore::class);
    expect(app(DefinitionStoreInterface::class))->toBeInstanceOf(DatabaseDefinitionStore::class);
});
