<?php

namespace App\Models;

use App\Contracts\BroadcastAiringInterface;
use App\Services\Guide;
use Carbon\Carbon;
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
            ->orderByPivot('starts_at')
            ->as('airing');
    }

    /**
     * Get full TV program airings (from 06:00:00 to next day 05:59:59).
     *
     * @param string $date
     * @return Collection
     */
    public function airingsOn(string $date): Collection
    {
        $date = CarbonImmutable::parse($date);

        /**
         * When current daily program is being queried, it uses current time.
         * If this time is after midnight, we have to take into account the
         * 6:00:00 boundary which indicates previous or next day schedule.
         */
        if ($date->isBetween($date->setTime(0, 0), $date->setTime(6, 0))) {
            $date = $date->subDay();
        }

        return $this
            ->broadcasts()
            /**
             * Date filtering works with inclusive 'from' and exclusive 'end' dates,
             * as 06:00:00 already denotes next days' TV program.
             */
            ->wherePivot('starts_at', '>=', $date->setTime(6, 0))
            ->wherePivot('ends_at', '<', $date->setTime(6, 0)->addDay())
            ->get()
            ->values();
    }

    /**
     * Get broadcast that is currently on-air according to current time.
     *
     * @return Broadcast|null
     */
    public function currentlyAiring(): ?Broadcast
    {
        $airings = $this
            ->airingsOn($now = now()->toImmutable());

        return (new Guide($airings))
            ->compile()
            ->first(static function (Broadcast $broadcast) use ($now) {
                return Carbon::parse($broadcast->airing->starts_at)->lt($now)
                    && Carbon::parse($broadcast->airing->ends_at)->gt($now);
            });
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
                'starts_at' => $airing->getAiringDatetime()->start(),
                'ends_at'   => $airing->getAiringDatetime()->end(),
            ]
        );

        return $this->broadcasts()->find($broadcast->id);
    }
}
