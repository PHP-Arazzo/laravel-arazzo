<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests\Support;

use Alama\Arazzo\Laravel\Support\AsyncGraphResolver;
use Alama\Arazzo\Runner\AsyncRunner;
use Illuminate\Contracts\Container\Container;

it('resolve returns null when AsyncRunner is not bound', function (): void {
    $app = \Mockery::mock(Container::class);
    $app->shouldReceive('bound')->with(AsyncRunner::class)->andReturn(false);

    expect(AsyncGraphResolver::resolve($app))->toBeNull();
});

it('resolve returns AsyncRunner when bound', function (): void {
    $runner = AsyncRunner::create();
    $app = \Mockery::mock(Container::class);
    $app->shouldReceive('bound')->with(AsyncRunner::class)->andReturn(true);
    $app->shouldReceive('make')->with(AsyncRunner::class)->andReturn($runner);

    expect(AsyncGraphResolver::resolve($app))->toBe($runner);
});
