<?php

declare(strict_types=1);

namespace Alama\Arazzo\Laravel\Models;

use Alama\Arazzo\Laravel\Support\Config;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $correlation_id
 * @property string $execution_id
 * @property string $step_id
 * @property string $channel_path
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 *
 * @internal stays out of the advertised contract; not part of the public API surface
 */
class ArazzoPendingCorrelation extends Model
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::pendingCorrelationsTable();
    }
}
