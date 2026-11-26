<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests;

use Alama\Arazzo\Laravel\RpcServiceProvider;
use Alama\Arazzo\Protocol\Rpc\Execution\RpcRequestBuilder;
use Alama\Arazzo\Protocol\Rpc\Plugins\SourceNormalizer\ProtoSourceNormalizer;
use Alama\Arazzo\Protocol\Rpc\Protocol\ProtobufOperationResolver;
use Alama\Arazzo\Protocol\Rpc\Protocol\RpcMessageCodec;
use Alama\Arazzo\Sources\Resolver\Interfaces\SourceFetcher;
use Illuminate\Support\ServiceProvider;

it('the RPC service provider class exists', function (): void {
    expect(class_exists(RpcServiceProvider::class))->toBeTrue();
});

it('the RPC service provider extends Illuminate ServiceProvider', function (): void {
    $reflection = new \ReflectionClass(RpcServiceProvider::class);
    expect($reflection->isSubclassOf(ServiceProvider::class))->toBeTrue();
});

it('ProtoSourceNormalizer is instantiable', function (): void {
    expect(new ProtoSourceNormalizer)->toBeInstanceOf(ProtoSourceNormalizer::class);
});

it('RpcMessageCodec is instantiable', function (): void {
    expect(new RpcMessageCodec)->toBeInstanceOf(RpcMessageCodec::class);
});

it('register method binds value seams without throwing', function (): void {
    $provider = new RpcServiceProvider($this->app);
    $provider->register();

    expect($this->app->bound(SourceFetcher::class))->toBeTrue();
    expect($this->app->bound(ProtoSourceNormalizer::class))->toBeTrue();
    expect($this->app->bound(ProtobufOperationResolver::class))->toBeTrue();
    expect($this->app->bound(RpcRequestBuilder::class))->toBeTrue();
});
