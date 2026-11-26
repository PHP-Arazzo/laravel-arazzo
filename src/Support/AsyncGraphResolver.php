<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Support;

use Alama\Arazzo\Runner\AsyncRunner;
use Illuminate\Contracts\Container\Container;

/**
 * Async graph resolver: resolves the container's async execution graph to an
 * AsyncRunner instance. Kept for backward compatibility with existing callers;
 * new code should use AsyncRunner directly from the container.
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
final class AsyncGraphResolver
{
    public static function resolve(Container $app): ?AsyncRunner
    {
        return $app->bound(AsyncRunner::class) ? $app->make(AsyncRunner::class) : null;
    }
}
