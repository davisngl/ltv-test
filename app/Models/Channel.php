<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Channel
 *
 * @property int $id
 * @property int $number
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Broadcast> $broadcasts
 * @property-read int|null $broadcasts_count
 * @method static \Database\Factories\ChannelFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Channel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Channel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Channel query()
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Channel whereNumber($value)
 * @mixin \Eloquent
 */
class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
    ];

    public function broadcasts(): BelongsToMany
    {
        return $this
            ->belongsToMany(Broadcast::class)
            ->withPivot([
                'starts_at',
                'ends_at',
            ]);
    }
}
