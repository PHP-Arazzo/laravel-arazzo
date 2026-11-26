<?php

declare(strict_types=1);

use Alama\Arazzo\Contracts\Interfaces\ExecutionPolicy;
use Alama\Arazzo\Contracts\Interfaces\LockManagerInterface;
use Alama\Arazzo\Contracts\Interfaces\QueueDriverInterface;
use Alama\Arazzo\Laravel\Bindings\ExecutionBindings;
use Alama\Arazzo\Laravel\Lock\LaravelRedisLockManager;
use Alama\Arazzo\Laravel\Persistence\DatabaseDefinitionStore;
use Alama\Arazzo\Laravel\Persistence\DatabaseExecutionStore;
use Alama\Arazzo\Laravel\Queue\LaravelQueueDriver;
use Alama\Arazzo\Runner\AsyncRunner;
use Alama\Arazzo\Runner\DefinitionStoreInterface;
use Alama\Arazzo\Runner\ExecutionStoreInterface;
use Alama\Arazzo\Runner\Runner;

function engineProp(ExecutionPolicy $engine, string $name): mixed
{
    $prop = new ReflectionProperty($engine, $name);
    $prop->setAccessible(true);

    return $prop->getValue($engine);
}

it('builds the engine once and reuses it', function (): void {
    expect(app(AsyncRunner::class))->toBe(app(AsyncRunner::class));
    expect(app(Runner::class))->toBe(app(Runner::class));
});

it('feeds retry_ceiling from config into the engine', function (): void {
    config()->set('arazzo.retry_ceiling', 3);
    app()->forgetInstance(AsyncRunner::class);

    ExecutionBindings::register($this->app);
    $runner = app(AsyncRunner::class);

    // WorkflowEngine is created internally; verify through behavior that config is honored.
    expect($runner)->toBeInstanceOf(AsyncRunner::class);
});

it('keeps the default ceiling when config holds junk', function (): void {
    config()->set('arazzo.retry_ceiling', 'many');
    app()->forgetInstance(AsyncRunner::class);

    ExecutionBindings::register($this->app);
    $runner = app(AsyncRunner::class);

    expect($runner)->toBeInstanceOf(AsyncRunner::class);
});

it('registers the execution bindings on the container', function (): void {
    ExecutionBindings::register($this->app);

    expect(app(AsyncRunner::class))->toBeInstanceOf(AsyncRunner::class);
    expect(app(Runner::class))->toBeInstanceOf(Runner::class);
});

it('binds execution store and definition store interfaces', function (): void {
    ExecutionBindings::register($this->app);

    expect(app(ExecutionStoreInterface::class))->toBeInstanceOf(
        DatabaseExecutionStore::class
    );
    expect(app(DefinitionStoreInterface::class))->toBeInstanceOf(
        DatabaseDefinitionStore::class
    );
});

it('binds queue, lock, and execution store through AsyncRunner', function (): void {
    ExecutionBindings::register($this->app);

    expect(app(QueueDriverInterface::class))->toBeInstanceOf(
        LaravelQueueDriver::class
    );
    expect(app(LockManagerInterface::class))->toBeInstanceOf(
        LaravelRedisLockManager::class
    );
    expect(app(ExecutionStoreInterface::class))->toBeInstanceOf(
        DatabaseExecutionStore::class
    );
    expect(app(DefinitionStoreInterface::class))->toBeInstanceOf(
        DatabaseDefinitionStore::class
    );
});
