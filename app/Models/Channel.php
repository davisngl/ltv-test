<?php

namespace App\Models;

use App\Contracts\BroadcastAiringInterface;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
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
            ->withPivot(['starts_at', 'ends_at'])
            ->as('airing');
    }

    /**
     * Get full TV program airings (from 06:00:00 to next day 05:59:59).
     *
     * @param CarbonImmutable $date
     * @return Collection
     */
    public function airingsOn(CarbonImmutable $date): Collection
    {
        $fromDatetime = $date->setTime(hour: 6, minute: 0);
        $endDatetime = $fromDatetime->addDay();

        return $this
            ->broadcasts()
            /**
             * Date filtering works with inclusive 'from' and exclusive 'end' dates,
             * as 06:00:00 already denotes next days' TV program.
             */
            ->wherePivot('starts_at', '>=', $fromDatetime)
            ->wherePivot('ends_at', '<', $endDatetime)
            ->orderByPivot('starts_at')
            ->get();
    }

    /**
     * Get broadcast that is currently on-air according to current time.
     *
     * @return Broadcast|null
     */
    public function currentlyAiring(): ?Broadcast
    {
        return $this
            ->broadcasts()
            ->wherePivot('starts_at', '>=', now())
            ->wherePivot('ends_at', '>', now())
            ->first();
    }

    /**
     * Get upcoming broadcasts for current channel.
     *
     * @param int $amount Amount of upcoming broadcasts to pick
     * @return Collection Collection of ordered broadcasts
     */
    public function upcomingBroadcasts(int $amount = 10): Collection
    {
        return $this
            ->broadcasts()
            /**
             * As long as no overlaps are allowed on controller side,
             * these broadcasts won't overlap, allowing us to look
             * for broadcasts that haven't ended according to current time
             * and whichever will follow after that.
             */
            ->wherePivot('ends_at', '>=', now())
            /**
             * If daily program is about to end and there are not enough of broadcasts,
             * it should not pick up next days' scheduled broadcasts.
             */
            ->wherePivot('starts_at', '<', now()->toImmutable()->setTime(6, 0)->addDay())
            ->wherePivot('starts_at', '>', now())
            ->orderByPivot('starts_at')
            ->limit($amount)
            ->get();
    }

    /**
     * @param BroadcastAiringInterface $airing Payload for broadcast airing data
     * @return Broadcast The broadcast that was just created
     */
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
