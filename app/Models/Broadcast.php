<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Broadcast
 *
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Channel> $channels
 * @property-read int|null $channels_count
 * @method static \Database\Factories\BroadcastFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast query()
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereName($value)
 * @mixin \Eloquent
 */
class Broadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function channels(): BelongsToMany
    {
        return $this
            ->belongsToMany(Channel::class)
            ->withPivot([
                'starts_at',
                'ends_at',
            ]);
    }
}
