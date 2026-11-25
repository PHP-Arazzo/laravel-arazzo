<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Queue;

use Alama\Arazzo\Contracts\Interfaces\QueueDriverInterface;
use Alama\Arazzo\Laravel\Queue\Jobs\RunExecuteStepJob;
use Alama\Arazzo\Runner\Data\ExecuteStepJob;
use Illuminate\Support\Facades\Queue;

/**
 * @internal stays out of the advertised contract; not part of the public API surface
 */
class LaravelQueueDriver implements QueueDriverInterface
{
    public function dispatch(object $job, int $delaySeconds = 0): void
    {
        $wrapped = match (true) {
            $job instanceof ExecuteStepJob => new RunExecuteStepJob($job),
            default => $job,
        };

        if ($delaySeconds > 0) {
            Queue::later(now()->addSeconds($delaySeconds), $wrapped);
        } else {
            Queue::push($wrapped);
        }
    }
}
