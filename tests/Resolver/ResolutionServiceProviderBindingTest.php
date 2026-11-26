<?php

declare(strict_types=1);

namespace Alama\Arazzo\Tests\Resolution;

use Alama\Arazzo\Contracts\Interfaces\SourceResolverInterface;
use Alama\Arazzo\Laravel\Bindings\FacadeBindings;
use Alama\Arazzo\Sources\Sources;

it('resolves SourceResolverInterface from the container as a Sources', function (): void {
    FacadeBindings::register($this->app);

    $resolver = $this->app->make(SourceResolverInterface::class);

    expect($resolver)->toBeInstanceOf(Sources::class);
});
