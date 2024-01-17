<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
