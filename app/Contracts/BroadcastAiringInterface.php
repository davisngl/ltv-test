<?php

namespace App\Contracts;

use Spatie\Period\Period;

interface BroadcastAiringInterface
{
    public function getBroadcastName(): string;

    /**
     * @return Period Broadcasts' start time and end time if it's given
     */
    public function getAiringDatetime(): Period;
}
