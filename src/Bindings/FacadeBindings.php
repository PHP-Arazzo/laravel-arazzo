<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Bindings;

use Alama\Arazzo\Contracts\Interfaces\SourceResolverInterface;
use Alama\Arazzo\Document\Document;
use Alama\Arazzo\Document\DocumentInterface;
use Alama\Arazzo\Evaluation\EvaluationEngine;
use Alama\Arazzo\Evaluation\EvaluationEngineInterface;
use Alama\Arazzo\Expression\ExpressionEngine;
use Alama\Arazzo\Expression\Interfaces\ExpressionEngineInterface;
use Alama\Arazzo\Protocol\Http\Resolver\OpenApiOperationResolver;
use Alama\Arazzo\Sources\Sources;
use Illuminate\Contracts\Container\Container;
use Psr\EventDispatcher\EventDispatcherInterface;

/** Entry-point facade bindings: document, expression, evaluation, sources, and operation resolution.
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
final class FacadeBindings
{
    public static function register(Container $app): void
    {
        $app->singleton(ExpressionEngineInterface::class, static fn (): ExpressionEngine => new ExpressionEngine);
        $app->singleton(EvaluationEngineInterface::class, fn (Container $app): EvaluationEngine => new EvaluationEngine(
            expression: $app->make(ExpressionEngineInterface::class),
            eventDispatcher: $app->make(EventDispatcherInterface::class),
        ));
        $app->singleton(Sources::class, fn (Container $app): Sources => Sources::create());
        $app->singleton(SourceResolverInterface::class, fn (Container $app) => $app->make(Sources::class));
        $app->singleton(DocumentInterface::class, fn (Container $app): DocumentInterface => new Document);
        $app->singleton(OpenApiOperationResolver::class, fn (Container $app): OpenApiOperationResolver => OpenApiOperationResolver::create($app->make(Sources::class)));
    }
}
