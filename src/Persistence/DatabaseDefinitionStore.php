<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Persistence;

use Alama\Arazzo\Contracts\Spec\ArazzoDocument;
use Alama\Arazzo\Runner\DefinitionStoreInterface;

/**
 * Durable definition store: wraps DatabaseDefinitionRegistry to implement the
 * consolidated DefinitionStoreInterface that replaces DefinitionRegistryInterface.
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
final class DatabaseDefinitionStore implements DefinitionStoreInterface
{
    public function __construct(
        private DatabaseDefinitionRegistry $registry,
    ) {}

    public function find(string $definitionId): ?ArazzoDocument
    {
        return $this->registry->get($definitionId);
    }

    public function save(ArazzoDocument $document, ?string $definitionId = null): string
    {
        return $this->registry->register($document);
    }
}
