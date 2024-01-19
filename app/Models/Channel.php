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
            ->as('airing');
    }

    public function airingsOn(CarbonImmutable $date): BelongsToMany
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
            ->orderByPivot('starts_at');
    }

    public function currentlyAiring(): ?Broadcast
    {
        return $this
            ->broadcasts()
            ->wherePivot('starts_at', '>=', now())
            ->wherePivot('ends_at', '>', now())
            ->first();
    }

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
