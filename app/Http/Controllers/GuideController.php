<?php

namespace App\Http\Controllers;

use App\DTO\BroadcastAiring;
use App\Http\Requests\ComposeGuideRequest;
use App\Models\Channel;
use Carbon\CarbonPeriod;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class GuideController extends Controller
{
    public function guideForDay(Channel $channel, Carbon $date)
    {
        dd($channel, $date);
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
