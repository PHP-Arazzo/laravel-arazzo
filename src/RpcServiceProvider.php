<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel;

use Alama\Arazzo\Evaluation\EvaluationEngineInterface;
use Alama\Arazzo\Laravel\Support\SchemeSourceFetcher;
use Alama\Arazzo\Protocol\Rpc\Execution\RpcRequestBuilder;
use Alama\Arazzo\Protocol\Rpc\Plugins\SourceNormalizer\ProtoSourceNormalizer;
use Alama\Arazzo\Protocol\Rpc\Protocol\ProtobufOperationResolver;
use Alama\Arazzo\Protocol\Rpc\Protocol\RpcMessageCodec;
use Alama\Arazzo\Sources\Resolver\Interfaces\SourceFetcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Wires the RPC vertical slice into the composed app: value-seam singletons
 * and the protobuf operation resolver.
 *
 * Note: RpcRegistrar and ResponseValidatorDispatcher were removed from the
 * public API; the RPC operation executor is not bound here. Register it
 * manually if needed.
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
final class RpcServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/arazzo.php', 'arazzo');

        $this->app->singleton(
            SourceFetcher::class,
            static fn (Container $app): SchemeSourceFetcher => SchemeSourceFetcher::fromContainer($app),
        );
        $this->app->singleton(
            ProtoSourceNormalizer::class,
            static fn (): ProtoSourceNormalizer => new ProtoSourceNormalizer,
        );
        $this->app->singleton(
            ProtobufOperationResolver::class,
            static fn (Container $app): ProtobufOperationResolver => new ProtobufOperationResolver(
                $app->make(SourceFetcher::class),
                $app->make(ProtoSourceNormalizer::class),
            ),
        );

        $this->app->singleton(
            RpcRequestBuilder::class,
            static fn (Container $app): RpcRequestBuilder => new RpcRequestBuilder(
                requestFactory: $app->make(RequestFactoryInterface::class),
                streamFactory: $app->make(StreamFactoryInterface::class),
                evaluationEngine: $app->make(EvaluationEngineInterface::class),
                codec: new RpcMessageCodec,
            ),
        );
    }
}
