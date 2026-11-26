<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests\Persistence;

use Alama\Arazzo\Contracts\Spec\ArazzoDocument;
use Alama\Arazzo\Laravel\Persistence\DatabaseDefinitionRegistry;
use Alama\Arazzo\Laravel\Persistence\DatabaseDefinitionStore;

it('DatabaseDefinitionStore.find delegates to registry get', function (): void {
    $registry = \Mockery::mock(DatabaseDefinitionRegistry::class);
    $registry->shouldReceive('get')->with('def-1')->andReturnNull();

    $store = new DatabaseDefinitionStore($registry);
    expect($store->find('def-1'))->toBeNull();
});

it('DatabaseDefinitionStore.save delegates to registry register', function (): void {
    $registry = \Mockery::mock(DatabaseDefinitionRegistry::class);
    $registry->shouldReceive('register')->with(\Mockery::type(ArazzoDocument::class))->andReturn('def-1');

    $store = new DatabaseDefinitionStore($registry);
    $document = definitionDocumentFor(definitionRawRoot());
    expect($store->save($document))->toBe('def-1');
});

it('DatabaseDefinitionStore.save with explicit definitionId delegates to registry register', function (): void {
    $registry = \Mockery::mock(DatabaseDefinitionRegistry::class);
    $registry->shouldReceive('register')->with(\Mockery::type(ArazzoDocument::class))->andReturn('custom-id');

    $store = new DatabaseDefinitionStore($registry);
    $document = definitionDocumentFor(definitionRawRoot());
    expect($store->save($document, 'custom-id'))->toBe('custom-id');
});
