<?php

namespace App\Http\Controllers;

use App\DTO\BroadcastAiring;
use App\Exceptions\DateFilterException;
use App\Http\Requests\ComposeGuideRequest;
use App\Http\Resources\BroadcastResource;
use App\Http\Resources\BroadcastResourceCollection;
use App\Models\Channel;
use App\Services\Guide;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Response;
use Spatie\Period\Period;

class GuideController extends Controller
{
    /**
     * @param Channel $channel
     * @param string $date
     * @return BroadcastResourceCollection
     */
    public function guideForDay(Channel $channel, string $date)
    {
        try {
            $date = CarbonImmutable::createFromFormat('Y-m-d', $date);
        } catch (InvalidFormatException) {
            throw DateFilterException::incorrectDateFormatSupplied();
        }

        return BroadcastResourceCollection::make(
            (new Guide($channel->airingsOn($date)->values()))->compile()
        );
    }

    /**
     * @param ComposeGuideRequest $request
     * @return mixed
     */
    public function composeGuide(ComposeGuideRequest $request)
    {
        $airing = new BroadcastAiring(
            broadcastName: $request->input('broadcast_name'),
            datetime: Period::make(start: $request->input('starts_at'), end: $request->input('ends_at'))
        );

        $request
            ->getChannel()
            ->addBroadcast($airing);

        return response()->success(
            message: 'Broadcast airing added successfully',
            status: Response::HTTP_CREATED
        );
    }

    /**
     * @param Channel $channel
     * @return mixed
     */
    public function currentBroadcast(Channel $channel)
    {
        $currentlyOnAir = $channel->currentlyAiring();

        if (! $currentlyOnAir) {
            return response()->failure(
                message: 'Nothing on air currently. Is the TV guide set for the day?',
                status: Response::HTTP_NOT_FOUND
            );
        }

        /**
         * There are some issues wrapping singular model resource
         * as equivalent '<singular resource>Collection' would automagically
         * pick up singular model resource as a wrapper for collection items.
         * Potentially, some package already elegantly fixes this issue with good DX.
         */
        return response()->success(
            message: 'Current broadcast retrieved successfully',
            data: BroadcastResource::make($currentlyOnAir)
        );
    }

    /**
     * @param Channel $channel
     * @return BroadcastResourceCollection
     */
    public function upcomingBroadcasts(Channel $channel)
    {
        return BroadcastResourceCollection::make($channel->upcomingBroadcasts());
    }
}
