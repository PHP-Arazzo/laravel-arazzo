<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests;

use Alama\Arazzo\Contracts\Interfaces\ReplacementTargetResolverInterface;
use Alama\Arazzo\Laravel\SoapServiceProvider;
use Alama\Arazzo\Protocol\Soap\Execution\SoapRequestBuilder;
use Alama\Arazzo\Protocol\Soap\Execution\WsdlOperationResolver;
use Alama\Arazzo\Protocol\Soap\Plugins\SourceNormalizer\WsdlSourceNormalizer;
use Alama\Arazzo\Protocol\Soap\Resolver\XPathReplacementTargetResolver;
use Alama\Arazzo\Sources\Resolver\Interfaces\SourceFetcher;
use Illuminate\Support\ServiceProvider;

it('the SOAP service provider class exists', function (): void {
    expect(class_exists(SoapServiceProvider::class))->toBeTrue();
});

it('the SOAP service provider extends Illuminate ServiceProvider', function (): void {
    $reflection = new \ReflectionClass(SoapServiceProvider::class);
    expect($reflection->isSubclassOf(ServiceProvider::class))->toBeTrue();
});

it('WsdlSourceNormalizer is instantiable', function (): void {
    expect(new WsdlSourceNormalizer)->toBeInstanceOf(WsdlSourceNormalizer::class);
});

it('XPathReplacementTargetResolver is instantiable', function (): void {
    expect(new XPathReplacementTargetResolver)->toBeInstanceOf(XPathReplacementTargetResolver::class);
});

it('ReplacementTargetResolverInterface exists', function (): void {
    expect(interface_exists(ReplacementTargetResolverInterface::class))->toBeTrue();
});

it('register method binds value seams without throwing', function (): void {
    $provider = new SoapServiceProvider($this->app);
    $provider->register();

    expect($this->app->bound(SourceFetcher::class))->toBeTrue();
    expect($this->app->bound(WsdlSourceNormalizer::class))->toBeTrue();
    expect($this->app->bound(WsdlOperationResolver::class))->toBeTrue();
    expect($this->app->bound(XPathReplacementTargetResolver::class))->toBeTrue();
    expect($this->app->bound(ReplacementTargetResolverInterface::class))->toBeTrue();
    expect($this->app->bound(SoapRequestBuilder::class))->toBeTrue();
});
