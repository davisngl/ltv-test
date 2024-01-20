<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperBroadcast
 */
class Broadcast extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function channels(): BelongsToMany
    {
        return $this
            ->belongsToMany(Channel::class)
            ->as('airing')
            ->withPivot([
                'starts_at',
                'ends_at',
            ]);
    }
}
