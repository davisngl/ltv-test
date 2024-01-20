<?php

namespace App\Http\Controllers;

use App\DTO\BroadcastAiring;
use App\Exceptions\DateFilterException;
use App\Http\Requests\ComposeGuideRequest;
use App\Http\Resources\BroadcastResource;
use App\Http\Resources\BroadcastResourceCollection;
use App\Models\Broadcast;
use App\Models\Channel;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Response;

class GuideController extends Controller
{
    public function guideForDay(Channel $channel, string $date)
    {
        try {
            $date = CarbonImmutable::createFromFormat('Y-m-d', $date);
        } catch (InvalidFormatException) {
            throw DateFilterException::incorrectDateFormatSupplied();
        }

        $airings = $channel
            ->airingsOn($date)
            ->values();

        $airings
            ->map(function (Broadcast $broadcast, int $order) use (&$airings) {
                $nextBroadcast = $airings->get($order + 1);

                if (! $nextBroadcast) {
                    return $broadcast->toArray();
                }

                $broadcast->airing->ends_at = $nextBroadcast->airing->starts_at;

                return $broadcast->toArray();
            });

        return BroadcastResourceCollection::make($airings);
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

        return response()->success(
            data: BroadcastResource::make($currentlyOnAir)
        );
    }

    public function upcomingBroadcasts(Channel $channel)
    {
        return BroadcastResourceCollection::make($channel->upcomingBroadcasts());
    }
}
