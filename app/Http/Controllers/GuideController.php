<?php

namespace App\Http\Controllers;

use App\DTO\BroadcastAiring;
use App\Exceptions\DateFilterException;
use App\Http\Requests\ComposeGuideRequest;
use App\Http\Resources\BroadcastResource;
use App\Http\Resources\BroadcastResourceCollection;
use App\Models\Channel;
use App\Services\CompilableGuideInterface;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Response;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;

class GuideController extends Controller
{
    /**
     * @return BroadcastResourceCollection
     */
    public function guideForDay(Channel $channel, string $date)
    {
        try {
            $date = Carbon::createFromFormat('Y-m-d', $date)->setTimeFrom(now());
        } catch (InvalidFormatException) {
            throw DateFilterException::incorrectDateFormatSupplied();
        }

        $guide = app(
            CompilableGuideInterface::class,
            [$channel->airingsOn($date)]
        )->compile();

        return BroadcastResourceCollection::make($guide);
    }

    /**
     * @return mixed
     */
    public function composeGuide(ComposeGuideRequest $request)
    {
        $airing = new BroadcastAiring(
            broadcastName: $request->input('broadcast_name'),
            datetime: Period::make(
                start: $request->input('starts_at'),
                end: $request->input('ends_at'),
                precision: Precision::SECOND(),
                boundaries: Boundaries::EXCLUDE_ALL()
            )
        );

        try {
            $request
                ->getChannel()
                ->addBroadcast($airing);
        } catch (InvalidFormatException $e) {
            return response()->failure(message: $e->getMessage());
        }

        return response()->success(
            message: 'Broadcast airing added successfully',
            status: Response::HTTP_CREATED
        );
    }

    /**
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
     * @return BroadcastResourceCollection
     */
    public function upcomingBroadcasts(Channel $channel)
    {
        return BroadcastResourceCollection::make($channel->upcomingBroadcasts());
    }
}
