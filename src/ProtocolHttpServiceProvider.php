<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel;

use Alama\Arazzo\Contracts\Interfaces\OutputExtractorInterface;
use Alama\Arazzo\Contracts\Interfaces\ResponseValidatorInterface;
use Alama\Arazzo\Evaluation\EvaluationEngineInterface;
use Alama\Arazzo\Expression\Interfaces\ExpressionEngineInterface;
use Alama\Arazzo\Laravel\Support\ConfigValue;
use Alama\Arazzo\Protocol\Http\Execution\DefaultOpenApiExecutor;
use Alama\Arazzo\Protocol\Http\Execution\Interfaces\OpenApiExecutorInterface;
use Alama\Arazzo\Protocol\Http\Execution\ResponseSchemaValidator;
use Alama\Arazzo\Protocol\Http\Execution\StepOutputExtractor;
use Alama\Arazzo\Protocol\Http\Resolver\OpenApiOperationResolver;
use Alama\Arazzo\Runner\Registries\OperationExecutorRegistry;
use Alama\Arazzo\Sources\Registries\SourceNormalizerRegistry;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Wires the protocol-http vertical slice into the composed app: value-seam
 * singletons (owned here since F1.3 — ExecutionBindings no longer builds
 * them), the normalizer and executor registries.
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
final class ProtocolHttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/arazzo.php', 'arazzo');

        // These are *value* seams consumed by the graphs, not registry
        // entries, so they bind directly to their protocol-http
        // implementations.
        $this->app->singleton(DefaultOpenApiExecutor::class, function (Container $app): DefaultOpenApiExecutor {
            return new DefaultOpenApiExecutor(
                $app->make(ClientInterface::class),
                $app->make(RequestFactoryInterface::class),
                $app->bound(LoggerInterface::class) ? $app->make(LoggerInterface::class) : null,
            );
        });
        $this->app->singleton(
            OpenApiExecutorInterface::class,
            static fn (Container $app): OpenApiExecutorInterface => $app->make(DefaultOpenApiExecutor::class),
        );

        $this->app->singleton(StepOutputExtractor::class, function (Container $app): StepOutputExtractor {
            return new StepOutputExtractor(
                $app->make(OpenApiOperationResolver::class),
                $app->make(EvaluationEngineInterface::class),
                $app->make(ExpressionEngineInterface::class),
            );
        });
        $this->app->singleton(
            OutputExtractorInterface::class,
            static fn (Container $app): OutputExtractorInterface => $app->make(StepOutputExtractor::class),
        );

        $this->app->singleton(
            ResponseSchemaValidator::class,
            static fn (Container $app): ResponseSchemaValidator => new ResponseSchemaValidator(
                $app->make(OpenApiOperationResolver::class),
            ),
        );
        $this->app->singleton(
            ResponseValidatorInterface::class,
            static fn (Container $app): ResponseValidatorInterface => $app->make(ResponseSchemaValidator::class),
        );

        // Source normalizer registry — filled by protocol registrars when
        // present, otherwise empty.
        $this->app->singleton(
            SourceNormalizerRegistry::class,
            static fn (): SourceNormalizerRegistry => SourceNormalizerRegistry::create(),
        );

        // Operation executor registry — filled by protocol registrars when
        // present, otherwise empty.
        $this->app->singleton(
            OperationExecutorRegistry::class,
            static fn (): OperationExecutorRegistry => OperationExecutorRegistry::create(),
        );

        // Tag vocabulary.
        $tagVocabulary = ['arazzo.plugins.*' => 'arazzo.plugins.*', 'arazzo.plugins.operation-executor' => 'arazzo.plugins.operation-executor'];
        $this->app->tag(
            $tagVocabulary['arazzo.plugins.*'],
            ConfigValue::string(config('arazzo.plugins.tags.0'), 'arazzo.plugins.*'),
        );
        $this->app->tag(
            $tagVocabulary['arazzo.plugins.operation-executor'],
            ConfigValue::string(config('arazzo.plugins.operation_executor'), 'arazzo.plugins.operation-executor'),
        );
    }
}
