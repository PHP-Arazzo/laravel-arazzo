<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel;

use Alama\Arazzo\Laravel\Bindings\EventBindings;
use Alama\Arazzo\Laravel\Bindings\ExecutionBindings;
use Alama\Arazzo\Laravel\Bindings\FacadeBindings;
use Alama\Arazzo\Laravel\Bindings\GeneratorBindings;
use Alama\Arazzo\Laravel\Bindings\HttpBindings;
use Alama\Arazzo\Laravel\Bindings\PersistenceBindings;
use Alama\Arazzo\Laravel\Http\Controllers\ArazzoApiController;
use Alama\Arazzo\Laravel\Http\Controllers\WebhookResumeController;
use Alama\Arazzo\Laravel\Support\ConfigValue;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class LaravelArazzoServiceProvider extends PackageServiceProvider
{
    public function register(): void
    {
        parent::register();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-arazzo')
            ->hasConfigFile('arazzo')
            ->hasMigrations([
                'create_arazzo_definitions_table',
                'create_arazzo_executions_table',
                'create_arazzo_events_table',
                'update_arazzo_executions_table_add_status',
                'create_arazzo_pending_correlations_table',
            ])
            ->runsMigrations();
    }

    public function packageRegistered(): void
    {
        // Composition is grouped by domain; each registrar owns its own
        // wiring so core-module refactors stop leaking into this file.
        HttpBindings::register($this->app);
        EventBindings::register($this->app);
        PersistenceBindings::register($this->app);
        GeneratorBindings::register($this->app);
        ExecutionBindings::register($this->app);
        FacadeBindings::register($this->app);
    }

    public function packageBooted(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'arazzo');

        Route::get('/arazzo-builder', function () {
            /** @var view-string $view */
            $view = 'arazzo::arazzo';

            return view($view);
        })->middleware('web');

        Route::prefix(ConfigValue::string(config('arazzo.webhook_prefix'), 'api/arazzo'))
            ->middleware('api')
            ->group(function () {
                Route::get('/endpoints', [ArazzoApiController::class, 'endpoints']);
                Route::post('/generate', [ArazzoApiController::class, 'generate']);
                Route::post('/webhooks/{correlationId}', [WebhookResumeController::class, 'resume']);
            });
    }
}
