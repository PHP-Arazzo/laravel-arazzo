<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests\Support;

use Alama\Arazzo\Laravel\Support\SchemeSourceFetcher;
use Alama\Arazzo\Sources\Exceptions\SourceFetchException;
use Alama\Arazzo\Sources\Resolver\Interfaces\SourceFetcher;
use Illuminate\Contracts\Container\Container;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

it('fetch throws SourceFetchException when no fetcher is configured for the scheme', function (): void {
    $fetcher = new SchemeSourceFetcher([]);
    expect(fn () => $fetcher->fetch('ftp://example.com/file', ''))
        ->toThrow(SourceFetchException::class);
});

it('fetch delegates to configured fetcher for known scheme', function (): void {
    $local = \Mockery::mock(SourceFetcher::class);
    $local->shouldReceive('fetch')->with('file:///tmp/test.txt', '')->andReturn('file-content');

    $fetcher = new SchemeSourceFetcher(['file' => $local]);
    $result = $fetcher->fetch('file:///tmp/test.txt', '');
    expect($result)->toBe('file-content');
});

it('fetch falls back to file fetcher when URL has no scheme', function (): void {
    $local = \Mockery::mock(SourceFetcher::class);
    $local->shouldReceive('fetch')->with('/tmp/test.txt', '')->andReturn('path-content');

    $fetcher = new SchemeSourceFetcher(['file' => $local]);
    $result = $fetcher->fetch('/tmp/test.txt', '');
    expect($result)->toBe('path-content');
});

it('stores fetchers by scheme', function (): void {
    $fetcher1 = \Mockery::mock(SourceFetcher::class);
    $fetcher2 = \Mockery::mock(SourceFetcher::class);
    $schemeFetcher = new SchemeSourceFetcher(['ftp' => $fetcher1, 'http' => $fetcher2]);
    expect($schemeFetcher)->toBeInstanceOf(SchemeSourceFetcher::class);
});

it('fromContainer builds fetchers for file, http, and https schemes', function (): void {
    $client = \Mockery::mock(ClientInterface::class);
    $factory = \Mockery::mock(RequestFactoryInterface::class);
    $app = \Mockery::mock(Container::class);
    $app->shouldReceive('make')->with(ClientInterface::class)->andReturn($client);
    $app->shouldReceive('make')->with(RequestFactoryInterface::class)->andReturn($factory);

    $schemeFetcher = SchemeSourceFetcher::fromContainer($app);
    expect($schemeFetcher)->toBeInstanceOf(SchemeSourceFetcher::class);
});
