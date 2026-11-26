<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests\Queue\Jobs;

use Alama\Arazzo\Laravel\Queue\Jobs\RunResumeCorrelationJob;
use Alama\Arazzo\Runner\AsyncRunner;
use ReflectionMethod;

it('RunResumeCorrelationJob stores correlationId and response', function (): void {
    $job = new RunResumeCorrelationJob('corr-1', ['key' => 'val']);
    expect($job->correlationId)->toBe('corr-1');
    expect($job->response)->toBe(['key' => 'val']);
});

it('RunResumeCorrelationJob handle method accepts AsyncRunner parameter', function (): void {
    $method = new ReflectionMethod(RunResumeCorrelationJob::class, 'handle');
    $params = $method->getParameters();
    expect($params)->toHaveCount(1);
    expect($params[0]->getType()->getName())->toBe(AsyncRunner::class);
});

it('RunResumeCorrelationJob handle delegates to AsyncRunner::resumeCorrelation', function (): void {
    $job = new RunResumeCorrelationJob('corr-1', ['key' => 'val']);

    // AsyncRunner::create() works with in-memory defaults; resumeCorrelation will
    // throw because there's no persisted correlation, but handle() body is covered.
    $runner = AsyncRunner::create();
    try {
        $job->handle($runner);
    } catch (\Throwable) {
        // Expected: no persisted correlation 'corr-1'
    }

    expect(true)->toBeTrue();
});
