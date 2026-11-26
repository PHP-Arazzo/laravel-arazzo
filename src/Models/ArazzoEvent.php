<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Models;

use Alama\Arazzo\Laravel\Support\Config;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $execution_id
 * @property string $event_type
 * @property array<string, mixed> $payload
 * @property Carbon $created_at
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
class ArazzoEvent extends Model
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::eventsTable();
    }
}
