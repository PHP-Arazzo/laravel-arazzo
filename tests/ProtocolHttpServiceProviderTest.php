<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests;

use Alama\Arazzo\Laravel\ProtocolHttpServiceProvider;
use Alama\Arazzo\Protocol\Http\Execution\DefaultOpenApiExecutor;
use Alama\Arazzo\Protocol\Http\Execution\Interfaces\OpenApiExecutorInterface;
use Alama\Arazzo\Protocol\Http\Execution\ResponseSchemaValidator;
use Alama\Arazzo\Protocol\Http\Execution\StepOutputExtractor;
use Alama\Arazzo\Sources\Registries\SourceNormalizerRegistry;
use Illuminate\Support\ServiceProvider;

it('binds the protocol value seams to their protocol-http implementations', function (): void {
    expect(app(OpenApiExecutorInterface::class))->toBeInstanceOf(DefaultOpenApiExecutor::class)
        ->and(app(StepOutputExtractor::class))->toBeInstanceOf(StepOutputExtractor::class)
        ->and(app(ResponseSchemaValidator::class))->toBeInstanceOf(ResponseSchemaValidator::class);
});

it('resolves the source normalizer registry', function (): void {
    expect(app(SourceNormalizerRegistry::class))->toBeInstanceOf(SourceNormalizerRegistry::class);
});

it('the service provider class exists', function (): void {
    expect(class_exists(ProtocolHttpServiceProvider::class))->toBeTrue();
});

it('the service provider extends Illuminate ServiceProvider', function (): void {
    $reflection = new \ReflectionClass(ProtocolHttpServiceProvider::class);
    expect($reflection->isSubclassOf(ServiceProvider::class))->toBeTrue();
});
