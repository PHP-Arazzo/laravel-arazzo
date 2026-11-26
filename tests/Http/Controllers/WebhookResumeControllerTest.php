<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests\Http\Controllers;

use Alama\Arazzo\Contracts\Interfaces\PendingCorrelationRegistryInterface;
use Alama\Arazzo\Contracts\Spec\PendingCorrelation;
use Alama\Arazzo\Laravel\Queue\Jobs\RunResumeCorrelationJob;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\postJson;

class WebhookControllerMockPendingCorrelations implements PendingCorrelationRegistryInterface
{
    public ?PendingCorrelation $toReturn = null;

    public function create(string $correlationId, string $executionId, string $stepId, string $channelPath, ?int $timeoutSeconds = null): void {}

    public function findByCorrelationId(string $correlationId): ?PendingCorrelation
    {
        return $this->toReturn;
    }

    public function consume(string $correlationId): void {}

    public function existsForExecution(string $executionId): bool
    {
        return false;
    }
}

it('returns 404 and dispatches nothing when the correlation is unknown', function (): void {
    Queue::fake();
    $this->app->instance(PendingCorrelationRegistryInterface::class, new WebhookControllerMockPendingCorrelations);

    postJson('/api/arazzo/webhooks/unknown-corr', ['rideId' => 'r_1'])
        ->assertStatus(404);

    Queue::assertNothingPushed();
});

it('returns 202 and dispatches a RunResumeCorrelationJob when the correlation is found', function (): void {
    Queue::fake();
    $fake = new WebhookControllerMockPendingCorrelations;
    $fake->toReturn = new PendingCorrelation('corr_1', 'exec_1', 'wait-for-ride', 'channels/rides/created');
    $this->app->instance(PendingCorrelationRegistryInterface::class, $fake);

    postJson('/api/arazzo/webhooks/corr_1', ['rideId' => 'r_1'])
        ->assertStatus(202);

    Queue::assertPushed(RunResumeCorrelationJob::class, function (RunResumeCorrelationJob $pushed) {
        return $pushed->correlationId === 'corr_1' && $pushed->response === ['rideId' => 'r_1'];
    });
});
