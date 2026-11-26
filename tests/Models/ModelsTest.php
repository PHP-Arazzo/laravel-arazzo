<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Tests\Models;

use Alama\Arazzo\Laravel\Models\ArazzoDefinition;
use Alama\Arazzo\Laravel\Models\ArazzoEvent;
use Alama\Arazzo\Laravel\Models\ArazzoExecution;
use Alama\Arazzo\Laravel\Models\ArazzoPendingCorrelation;
use Alama\Arazzo\Laravel\Support\Config;

it('ArazzoDefinition is instantiable with correct table', function (): void {
    $model = new ArazzoDefinition;
    expect($model)->toBeInstanceOf(ArazzoDefinition::class);
    expect($model->getTable())->toBe(Config::definitionsTable());
});

it('ArazzoExecution is instantiable with correct table', function (): void {
    $model = new ArazzoExecution;
    expect($model)->toBeInstanceOf(ArazzoExecution::class);
    expect($model->getTable())->toBe(Config::executionsTable());
});

it('ArazzoEvent is instantiable with correct table', function (): void {
    $model = new ArazzoEvent;
    expect($model)->toBeInstanceOf(ArazzoEvent::class);
    expect($model->getTable())->toBe(Config::eventsTable());
});

it('ArazzoPendingCorrelation is instantiable with correct table', function (): void {
    $model = new ArazzoPendingCorrelation;
    expect($model)->toBeInstanceOf(ArazzoPendingCorrelation::class);
    expect($model->getTable())->toBe(Config::pendingCorrelationsTable());
});
