<?php

namespace App\Http\Controllers;

use App\DTO\BroadcastAiring;
use App\Exceptions\DateFilterException;
use App\Http\Requests\ComposeGuideRequest;
use App\Models\Broadcast;
use App\Models\Channel;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Http\Response;

class GuideController extends Controller
{
    public function guideForDay(Channel $channel, string $date)
    {
        $date = CarbonImmutable::createFromFormat('Y-m-d', $date);

        if (! $date) {
            throw DateFilterException::incorrectDateFormatSupplied();
        }

        $airings = $channel
            ->airingsOn($date)
            ->get(['name', 'starts_at', 'ends_at'])
            ->values();

        $airings
            ->map(function (Broadcast $broadcast, int $order) use (&$airings) {
                $nextBroadcast = $airings->get($order + 1);
                // Pivot values are dragged along the related model,
                // for cleaner output, we remove it.
                unset($broadcast->airing);

                if (! $nextBroadcast) {
                    return $broadcast->toArray();
                }

                $broadcast->ends_at = $nextBroadcast->starts_at;

                return $broadcast->toArray();
            });

        return response()->success(data: $airings);
    }

    public function composeGuide(ComposeGuideRequest $request)
    {
        $airing = new BroadcastAiring(
            broadcastName: $request->input('broadcast_name'),
            datetime: new CarbonPeriod(
                $request->input('starts_at'),
                $request->input('ends_at')
            )
        );

        $request
            ->getChannel()
            ->addBroadcast($airing);

        return response()->success(
            message: 'Broadcast airing successfully added',
            status: Response::HTTP_CREATED
        );
    }

    public function currentBroadcast(Channel $channel)
    {
        $currentlyOnAir = $channel->currentlyAiring();

        if (! $currentlyOnAir) {
            return response()->failure(
                message: 'Nothing on air currently. Is the TV guide set for the day?',
                status: 404
            );
        }

        return response()->success(data: $currentlyOnAir->toArray());
    }

    public function upcomingBroadcasts(Channel $channel)
    {
        return response()->json(data: $channel->upcomingBroadcasts());
    }
}
