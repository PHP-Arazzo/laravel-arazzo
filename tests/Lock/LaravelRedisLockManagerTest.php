<?php

declare(strict_types=1);

use Alama\Arazzo\Laravel\Lock\LaravelRedisLockManager;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;

it('blocks on the underlying cache lock and returns the callback result', function (): void {
    $lock = Mockery::mock(Lock::class);
    $lock->shouldReceive('block')
        ->once()
        ->with(5, Mockery::on(is_callable(...)))
        ->andReturnUsing(function (int $seconds, callable $callback): mixed {
            return $callback();
        });

    Cache::shouldReceive('lock')
        ->once()
        ->with('execution_lock_exec_1', 30)
        ->andReturn($lock);

    $result = (new LaravelRedisLockManager)->acquire('execution_lock_exec_1', 30, function (): string {
        return 'ran-under-lock';
    });

    expect($result)->toBe('ran-under-lock');
});

it('propagates exceptions thrown inside the locked callback', function (): void {
    $lock = Mockery::mock(Lock::class);
    $lock->shouldReceive('block')
        ->once()
        ->andReturnUsing(function (int $seconds, callable $callback): mixed {
            return $callback();
        });

    Cache::shouldReceive('lock')->andReturn($lock);

    (new LaravelRedisLockManager)->acquire('k', 10, function (): void {
        throw new RuntimeException('step exploded');
    });
})->throws(RuntimeException::class, 'step exploded');

it('tryAcquire returns true and stores lock when available', function (): void {
    $lock = Mockery::mock(Lock::class);
    $lock->shouldReceive('get')->once()->andReturn(true);

    Cache::shouldReceive('lock')->once()->with('my-key', 30)->andReturn($lock);

    $result = (new LaravelRedisLockManager)->tryAcquire('my-key', 30);

    expect($result)->toBeTrue();
});

it('tryAcquire returns false when lock is not available', function (): void {
    $lock = Mockery::mock(Lock::class);
    $lock->shouldReceive('get')->once()->andReturn(false);

    Cache::shouldReceive('lock')->once()->with('my-key', 30)->andReturn($lock);

    $result = (new LaravelRedisLockManager)->tryAcquire('my-key', 30);

    expect($result)->toBeFalse();
});

it('release removes and releases an owned lock', function (): void {
    $lock = Mockery::mock(Lock::class);
    $lock->shouldReceive('get')->once()->andReturn(true);
    $lock->shouldReceive('release')->once();

    Cache::shouldReceive('lock')->once()->with('my-key', 30)->andReturn($lock);

    $manager = new LaravelRedisLockManager;
    $manager->tryAcquire('my-key', 30);
    $manager->release('my-key');
});

it('release is a no-op for foreign keys without owner token', function (): void {
    Cache::shouldReceive('lock')->once()->with('foreign-key', 0)->andReturnUsing(function ($key, $ttl) {
        $mock = Mockery::mock(Lock::class);
        $mock->shouldReceive('release')->once();

        return $mock;
    });

    $manager = new LaravelRedisLockManager;
    $manager->release('foreign-key');
});
