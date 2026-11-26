<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Persistence;

use Alama\Arazzo\Contracts\Spec\Enum\ExecutionStatus;
use Alama\Arazzo\Contracts\Spec\PendingCorrelation;
use Alama\Arazzo\Laravel\Support\Config;
use Alama\Arazzo\Runner\ExecutionStoreInterface;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Database\ConnectionInterface;

/**
 * Durable execution store: hot state in Redis, execution lifecycle and pending
 * correlations in the database. Implements the consolidated ExecutionStoreInterface
 * that replaces StateStoreInterface + ExecutionRegistryInterface +
 * PendingCorrelationRegistryInterface + EventLedgerInterface.
 *
 * All dependencies are constructor-injected; the container resolves them
 * automatically. No manual `new`, no `$app->make()`, no `config()` helper.
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
final class DatabaseExecutionStore implements ExecutionStoreInterface
{
    public function __construct(
        private RedisFactory $redis,
        private ConnectionInterface $db,
        private Config $config,
    ) {}

    public function saveState(string $executionId, array $state, ?int $ttlSeconds = null): void
    {
        $this->redis->connection()->setex(
            'arazzo:state:'.$executionId,
            $ttlSeconds ?? $this->config->stateTtlSeconds(),
            json_encode($state),
        );
    }

    public function loadState(string $executionId): ?array
    {
        $data = $this->redis->connection()->get('arazzo:state:'.$executionId);
        if (! is_string($data) || $data === '') {
            return null;
        }
        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($data, true);

        return $decoded;
    }

    public function deleteState(string $executionId): void
    {
        $this->redis->connection()->del('arazzo:state:'.$executionId);
    }

    public function markStarted(string $executionId, string $definitionId, string $workflowId): void
    {
        $this->db->table($this->config->executionsTable())->insertOrIgnore([
            'id' => $executionId,
            'definition_id' => $definitionId,
            'workflow_id' => $workflowId,
            'status' => ExecutionStatus::Running->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function markCompleted(string $executionId, ExecutionStatus $status): void
    {
        $this->db->table($this->config->executionsTable())
            ->where('id', $executionId)
            ->where('status', ExecutionStatus::Running->value)
            ->update([
                'status' => $status->value,
                'completed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function saveCorrelation(
        string $correlationId,
        string $executionId,
        string $stepId,
        string $channelPath,
        ?int $timeoutSeconds = null,
    ): void {
        $this->db->table($this->config->pendingCorrelationsTable())->insert([
            'correlation_id' => $correlationId,
            'execution_id' => $executionId,
            'step_id' => $stepId,
            'channel_path' => $channelPath,
            'expires_at' => $timeoutSeconds !== null ? now()->addSeconds($timeoutSeconds) : null,
            'created_at' => now(),
        ]);
    }

    public function findCorrelation(string $correlationId): ?PendingCorrelation
    {
        $row = $this->db->table($this->config->pendingCorrelationsTable())
            ->where('correlation_id', $correlationId)
            ->first();

        if (! $row) {
            return null;
        }

        return new PendingCorrelation(
            correlationId: $this->textColumn($row->correlation_id, 'correlation_id'),
            executionId: $this->textColumn($row->execution_id, 'execution_id'),
            stepId: $this->textColumn($row->step_id, 'step_id'),
            channelPath: $this->textColumn($row->channel_path, 'channel_path'),
            expiresAt: $row->expires_at !== null && $row->expires_at instanceof \DateTimeInterface
                ? \DateTimeImmutable::createFromInterface($row->expires_at)
                : null,
        );
    }

    public function consumeCorrelation(string $correlationId): void
    {
        $this->db->table($this->config->pendingCorrelationsTable())
            ->where('correlation_id', $correlationId)
            ->delete();
    }

    public function hasPendingCorrelations(string $executionId): bool
    {
        return $this->db->table($this->config->pendingCorrelationsTable())
            ->where('execution_id', $executionId)
            ->exists();
    }

    /**
     * Laravel types query-builder column values as mixed. Every column read
     * back here is written as a string by create(), so anything else means the
     * row cannot be turned into a usable PendingCorrelation and we refuse to
     * invent one.
     */
    private function textColumn(mixed $value, string $column): string
    {
        if (! is_scalar($value)) {
            throw new \UnexpectedValueException("Pending correlation column '{$column}' is not a scalar, got ".get_debug_type($value).'.');
        }

        return (string) $value;
    }
}
