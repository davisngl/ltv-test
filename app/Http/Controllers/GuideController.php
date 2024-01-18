<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Support\Carbon;

class GuideController extends Controller
{
	public function guideForDay(Channel $channel, Carbon $date)
	{
        dd($channel, $date);
	}
}
