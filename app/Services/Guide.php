<?php

namespace App\Services;

use App\Models\Broadcast;
use Illuminate\Support\Collection;

readonly class Guide
{
    public function __construct(private Collection $airings)
    {
    }

    /**
     * Create a collection of broadcast airings for the day.
     *
     * @return Collection
     */
    public function compile(): Collection
    {
        return $this
            ->airings
            ->map(function (Broadcast $broadcast, int $order) {
                /**
                 * As 'values' method makes the array indexed by integer key,
                 * it is easy to access next item - no need for linked list
                 * or options that Iterable gives.
                 */
                $nextBroadcast = $this->airings->get($order + 1);

                if (! $nextBroadcast) {
                    /**
                     * Since it's the last airing of the daily program,
                     * there is no end date fix-up.
                     */
                    return $broadcast;
                }

                $broadcast->airing->ends_at = $nextBroadcast->airing->starts_at;

                return $broadcast;
            });
    }
}
