<?php

namespace App\Contracts;

use Carbon\CarbonPeriod;

interface BroadcastAiringInterface
{
    public function getBroadcastName(): string;

    /**
     * @return CarbonPeriod Broadcasts' start time and end time if it's given
     */
    public function getAiringDatetime(): CarbonPeriod;
}
