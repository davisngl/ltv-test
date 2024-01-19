<?php

namespace App\Models;

use App\Contracts\BroadcastAiringInterface;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


/**
 * @mixin IdeHelperChannel
 */
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
            ->as('airing')
            ->withPivot([
                'starts_at',
                'ends_at',
            ]);
    }

    public function airingsOn(CarbonImmutable $date): BelongsToMany
    {
        return $this
            ->belongsToMany(Broadcast::class)
            ->as('airing')
            ->withPivot([
                'starts_at',
                'ends_at',
            ])
            ->wherePivotBetween(
                'starts_at',
                [$date->setTime(hour: 6, minute: 0), $date->addDay()]
            )
            ->orderByPivot('starts_at');
    }

    public function addBroadcast(BroadcastAiringInterface $airing): Broadcast
    {
        $broadcast = Broadcast::firstOrCreate(['name' => $airing->getBroadcastName()]);

        $this->broadcasts()->attach(
            $broadcast,
            [
                'starts_at' => $airing->getAiringDatetime()->getStartDate(),
                'ends_at'   => $airing->getAiringDatetime()->getEndDate(),
            ]
        );

        return $this->broadcasts()->find($broadcast->id);
    }
}
