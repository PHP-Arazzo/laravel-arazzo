<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Models;

use Alama\Arazzo\Laravel\Support\Config;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $definition_id
 * @property string $workflow_id
 * @property string $status
 * @property Carbon|null $completed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
class ArazzoExecution extends Model
{
    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::executionsTable();
    }
}
