<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests\Persistence;

use Alama\Arazzo\Contracts\Spec\Enum\ExecutionStatus;
use Alama\Arazzo\Laravel\Persistence\DatabaseExecutionStore;
use Alama\Arazzo\Laravel\Support\Config;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Database\ConnectionInterface;

it('DatabaseExecutionStore is instantiable', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $redis->shouldReceive('connection')->andReturn($connection = \Mockery::mock());
    $connection->shouldReceive('setex')->andReturnNull();
    $connection->shouldReceive('get')->andReturnNull();
    $connection->shouldReceive('del')->andReturnNull();

    $db = \Mockery::mock(ConnectionInterface::class);
    $table = \Mockery::mock();
    $db->shouldReceive('table')->andReturn($table);
    $table->shouldReceive('insertOrIgnore')->andReturnNull();
    $table->shouldReceive('where')->andReturn($table);
    $table->shouldReceive('update')->andReturnNull();
    $table->shouldReceive('insert')->andReturnNull();
    $table->shouldReceive('delete')->andReturnNull();
    $table->shouldReceive('exists')->andReturnFalse();
    $table->shouldReceive('first')->andReturnNull();
    $table->shouldReceive('value')->andReturnNull();

    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    expect($store)->toBeInstanceOf(DatabaseExecutionStore::class);
});

it('DatabaseExecutionStore delegates saveState to redis', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $connection = \Mockery::mock();
    $connection->shouldReceive('setex')->with('arazzo:state:exec-1', 3600, \Mockery::type('string'))->once()->andReturnNull();
    $redis->shouldReceive('connection')->andReturn($connection);

    $db = \Mockery::mock(ConnectionInterface::class);
    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $store->saveState('exec-1', ['key' => 'val'], 3600);
});

it('DatabaseExecutionStore delegates loadState to redis', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $connection = \Mockery::mock();
    $connection->shouldReceive('get')->with('arazzo:state:exec-1')->andReturn('{"key":"val"}');
    $redis->shouldReceive('connection')->andReturn($connection);

    $db = \Mockery::mock(ConnectionInterface::class);
    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $result = $store->loadState('exec-1');
    expect($result)->toBe(['key' => 'val']);
});

it('DatabaseExecutionStore loadState returns null when data is not a string', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $connection = \Mockery::mock();
    $connection->shouldReceive('get')->with('arazzo:state:exec-1')->andReturn(123);
    $redis->shouldReceive('connection')->andReturn($connection);

    $db = \Mockery::mock(ConnectionInterface::class);
    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $result = $store->loadState('exec-1');
    expect($result)->toBeNull();
});

it('DatabaseExecutionStore delegates deleteState to redis', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $connection = \Mockery::mock();
    $connection->shouldReceive('del')->with('arazzo:state:exec-1')->once()->andReturnNull();
    $redis->shouldReceive('connection')->andReturn($connection);

    $db = \Mockery::mock(ConnectionInterface::class);
    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $store->deleteState('exec-1');
});

it('DatabaseExecutionStore delegates markStarted to db', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $redis->shouldReceive('connection')->andReturnNull();

    $db = \Mockery::mock(ConnectionInterface::class);
    $table = \Mockery::mock();
    $table->shouldReceive('insertOrIgnore')->once()->andReturnNull();
    $db->shouldReceive('table')->with('arazzo_executions')->andReturn($table);

    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $store->markStarted('exec-1', 'def-1', 'wf-1');
});

it('DatabaseExecutionStore delegates markCompleted to db', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $redis->shouldReceive('connection')->andReturnNull();

    $db = \Mockery::mock(ConnectionInterface::class);
    $table = \Mockery::mock();
    $table->shouldReceive('where')->with('id', 'exec-1')->andReturn($table);
    $table->shouldReceive('where')->with('status', 'running')->andReturn($table);
    $table->shouldReceive('update')->once()->andReturnNull();
    $db->shouldReceive('table')->with('arazzo_executions')->andReturn($table);

    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $store->markCompleted('exec-1', ExecutionStatus::Succeeded);
});

it('DatabaseExecutionStore delegates saveCorrelation to db', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $redis->shouldReceive('connection')->andReturnNull();

    $db = \Mockery::mock(ConnectionInterface::class);
    $table = \Mockery::mock();
    $table->shouldReceive('insert')->once()->andReturnNull();
    $db->shouldReceive('table')->with('arazzo_pending_correlations')->andReturn($table);

    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $store->saveCorrelation('corr-1', 'exec-1', 'step-1', 'channel/path', 300);
});

it('DatabaseExecutionStore delegates findCorrelation to db', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $redis->shouldReceive('connection')->andReturnNull();

    $db = \Mockery::mock(ConnectionInterface::class);
    $table = \Mockery::mock();
    $table->shouldReceive('where')->with('correlation_id', 'corr-1')->andReturn($table);
    $table->shouldReceive('first')->andReturn((object) [
        'correlation_id' => 'corr-1',
        'execution_id' => 'exec-1',
        'step_id' => 'step-1',
        'channel_path' => 'channel/path',
        'expires_at' => null,
    ]);
    $db->shouldReceive('table')->with('arazzo_pending_correlations')->andReturn($table);

    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $result = $store->findCorrelation('corr-1');
    expect($result)->not->toBeNull();
    expect($result->correlationId)->toBe('corr-1');
    expect($result->executionId)->toBe('exec-1');
});

it('DatabaseExecutionStore returns null from findCorrelation when not found', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $redis->shouldReceive('connection')->andReturnNull();

    $db = \Mockery::mock(ConnectionInterface::class);
    $table = \Mockery::mock();
    $table->shouldReceive('where')->with('correlation_id', 'corr-1')->andReturn($table);
    $table->shouldReceive('first')->andReturnNull();
    $db->shouldReceive('table')->with('arazzo_pending_correlations')->andReturn($table);

    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $result = $store->findCorrelation('corr-1');
    expect($result)->toBeNull();
});

it('DatabaseExecutionStore delegates consumeCorrelation to db', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $redis->shouldReceive('connection')->andReturnNull();

    $db = \Mockery::mock(ConnectionInterface::class);
    $table = \Mockery::mock();
    $table->shouldReceive('where')->with('correlation_id', 'corr-1')->andReturn($table);
    $table->shouldReceive('delete')->once()->andReturnNull();
    $db->shouldReceive('table')->with('arazzo_pending_correlations')->andReturn($table);

    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $store->consumeCorrelation('corr-1');
});

it('DatabaseExecutionStore delegates hasPendingCorrelations to db', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $redis->shouldReceive('connection')->andReturnNull();

    $db = \Mockery::mock(ConnectionInterface::class);
    $table = \Mockery::mock();
    $table->shouldReceive('where')->with('execution_id', 'exec-1')->andReturn($table);
    $table->shouldReceive('exists')->andReturnTrue();
    $db->shouldReceive('table')->with('arazzo_pending_correlations')->andReturn($table);

    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $result = $store->hasPendingCorrelations('exec-1');
    expect($result)->toBeTrue();
});

it('DatabaseExecutionStore textColumn throws for non-scalar value', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $redis->shouldReceive('connection')->andReturnNull();

    $db = \Mockery::mock(ConnectionInterface::class);
    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $method = new \ReflectionMethod($store, 'textColumn');
    $method->setAccessible(true);

    expect(fn () => $method->invoke($store, ['not', 'a', 'scalar'], 'test_column'))
        ->toThrow(\UnexpectedValueException::class);
});

it('DatabaseExecutionStore returns false for hasPendingCorrelations when none', function (): void {
    $redis = \Mockery::mock(RedisFactory::class);
    $redis->shouldReceive('connection')->andReturnNull();

    $db = \Mockery::mock(ConnectionInterface::class);
    $table = \Mockery::mock();
    $table->shouldReceive('where')->with('execution_id', 'exec-1')->andReturn($table);
    $table->shouldReceive('exists')->andReturnFalse();
    $db->shouldReceive('table')->with('arazzo_pending_correlations')->andReturn($table);

    $config = new Config;

    $store = new DatabaseExecutionStore($redis, $db, $config);
    $result = $store->hasPendingCorrelations('exec-1');
    expect($result)->toBeFalse();
});
