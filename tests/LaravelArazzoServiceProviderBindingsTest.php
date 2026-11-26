<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests;

use Alama\Arazzo\Cli\Generator\ArazzoGenerator;
use Alama\Arazzo\Cli\Generator\Clients\OpenAiClient;
use Alama\Arazzo\Contracts\Interfaces\AiClientInterface;
use Alama\Arazzo\Contracts\Interfaces\HttpClientInterface;
use Alama\Arazzo\Contracts\Interfaces\LockManagerInterface;
use Alama\Arazzo\Contracts\Interfaces\QueueDriverInterface;
use Alama\Arazzo\Contracts\Interfaces\SourceResolverInterface;
use Alama\Arazzo\Document\Document;
use Alama\Arazzo\Document\DocumentInterface;
use Alama\Arazzo\Evaluation\EvaluationEngine;
use Alama\Arazzo\Evaluation\EvaluationEngineInterface;
use Alama\Arazzo\Expression\ExpressionEngine;
use Alama\Arazzo\Expression\Interfaces\ExpressionEngineInterface;
use Alama\Arazzo\Laravel\Http\Psr18HttpClient;
use Alama\Arazzo\Laravel\Lock\LaravelRedisLockManager;
use Alama\Arazzo\Laravel\Persistence\DatabaseDefinitionStore;
use Alama\Arazzo\Laravel\Persistence\DatabaseExecutionStore;
use Alama\Arazzo\Laravel\Queue\LaravelQueueDriver;
use Alama\Arazzo\Runner\AsyncRunner;
use Alama\Arazzo\Runner\DefinitionStoreInterface;
use Alama\Arazzo\Runner\ExecutionStoreInterface;
use Alama\Arazzo\Runner\Runner;
use Alama\Arazzo\Sources\Sources;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

it('binds psr interfaces to guzzle', function () {
    expect(app(ClientInterface::class))->toBeInstanceOf(Client::class);
    expect(app(RequestFactoryInterface::class))->toBeInstanceOf(HttpFactory::class);
    expect(app(StreamFactoryInterface::class))->toBeInstanceOf(HttpFactory::class);
});

it('binds AiClientInterface', function () {
    expect(app(AiClientInterface::class))->toBeInstanceOf(OpenAiClient::class);
});

it('binds ArazzoGenerator', function () {
    expect(app(ArazzoGenerator::class))->toBeInstanceOf(ArazzoGenerator::class);
});

it('binds AsyncRunner and Runner execution facades', function () {
    expect(app(AsyncRunner::class))->toBeInstanceOf(AsyncRunner::class);
    expect(app(Runner::class))->toBeInstanceOf(Runner::class);
});

it('binds the execution store and definition store interfaces', function () {
    expect(app(ExecutionStoreInterface::class))->toBeInstanceOf(DatabaseExecutionStore::class);
    expect(app(DefinitionStoreInterface::class))->toBeInstanceOf(DatabaseDefinitionStore::class);
});

it('binds the queue/lock/http infra', function () {
    expect(app(HttpClientInterface::class))->toBeInstanceOf(Psr18HttpClient::class);
    expect(app(LockManagerInterface::class))->toBeInstanceOf(LaravelRedisLockManager::class);
    expect(app(QueueDriverInterface::class))->toBeInstanceOf(LaravelQueueDriver::class);
});

it('binds the entry-point facade interfaces to their self-contained facades', function () {
    expect(app(ExpressionEngineInterface::class))->toBeInstanceOf(ExpressionEngine::class);
    expect(app(EvaluationEngineInterface::class))->toBeInstanceOf(EvaluationEngine::class);
    expect(app(DocumentInterface::class))->toBeInstanceOf(Document::class);
});

it('binds source resolver interface', function () {
    expect(app(SourceResolverInterface::class))->toBeInstanceOf(
        Sources::class
    );
});
