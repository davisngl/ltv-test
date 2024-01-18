<?php

namespace App\Http\Controllers;

use App\DTO\BroadcastAiring;
use App\Exceptions\DateFilterException;
use App\Http\Requests\ComposeGuideRequest;
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

        return response()->success(data: $channel->airingsOn($date)->get());
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
}
