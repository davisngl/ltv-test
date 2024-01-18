<?php

namespace App\Models;

use App\Contracts\BroadcastAiringInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Channel extends Model
{
    use HasFactory;

    public $timestamps = false;

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

    public function addBroadcast(BroadcastAiringInterface $airing): Broadcast
    {
        $broadcast = Broadcast::firstOrCreate(['name' => $airing->getBroadcastName()]);

        $this->broadcasts()->attach(
            $broadcast,
            [
                'starts_at' => $airing->getAiringDatetime()?->getStartDate(),
                'ends_at'   => $airing->getAiringDatetime()?->getEndDate(),
            ]
        );

        return $broadcast;
    }
}
