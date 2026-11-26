<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel;

use Alama\Arazzo\Contracts\Interfaces\ReplacementTargetResolverInterface;
use Alama\Arazzo\Evaluation\EvaluationEngineInterface;
use Alama\Arazzo\Laravel\Support\SchemeSourceFetcher;
use Alama\Arazzo\Protocol\Soap\Execution\SoapRequestBuilder;
use Alama\Arazzo\Protocol\Soap\Execution\WsdlOperationResolver;
use Alama\Arazzo\Protocol\Soap\Plugins\SourceNormalizer\WsdlSourceNormalizer;
use Alama\Arazzo\Protocol\Soap\Resolver\XPathReplacementTargetResolver;
use Alama\Arazzo\Sources\Resolver\Interfaces\SourceFetcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Wires the SOAP vertical slice into the composed app: value-seam singletons
 * and the SOAP operation resolver.
 *
 * Note: SoapRegistrar and ResponseValidatorDispatcher were removed from the
 * public API; the SOAP operation executor and XSD validator are not bound
 * here. Register them manually if needed.
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
final class SoapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/arazzo.php', 'arazzo');

        $this->app->singleton(
            SourceFetcher::class,
            static fn (Container $app): SchemeSourceFetcher => SchemeSourceFetcher::fromContainer($app),
        );
        $this->app->singleton(
            WsdlSourceNormalizer::class,
            static fn (): WsdlSourceNormalizer => new WsdlSourceNormalizer,
        );
        $this->app->singleton(
            WsdlOperationResolver::class,
            static fn (Container $app): WsdlOperationResolver => new WsdlOperationResolver(
                $app->make(SourceFetcher::class),
                $app->make(WsdlSourceNormalizer::class),
            ),
        );

        $this->app->singleton(
            XPathReplacementTargetResolver::class,
            static fn (): XPathReplacementTargetResolver => new XPathReplacementTargetResolver,
        );
        $this->app->singleton(
            ReplacementTargetResolverInterface::class,
            static fn (Container $app): ReplacementTargetResolverInterface => $app->make(XPathReplacementTargetResolver::class),
        );

        $this->app->singleton(
            SoapRequestBuilder::class,
            static fn (Container $app): SoapRequestBuilder => new SoapRequestBuilder(
                requestFactory: $app->make(RequestFactoryInterface::class),
                streamFactory: $app->make(StreamFactoryInterface::class),
                evaluationEngine: $app->make(EvaluationEngineInterface::class),
                replacementResolver: $app->make(ReplacementTargetResolverInterface::class),
            ),
        );
    }
}
