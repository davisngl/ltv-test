<?php

namespace App\DTO;

use App\Contracts\BroadcastAiringInterface;
use Carbon\CarbonPeriod;

readonly class BroadcastAiring implements BroadcastAiringInterface
{
    public function __construct(
        protected string $broadcastName,
        protected CarbonPeriod $datetime
    ) {
    }

    public function getBroadcastName(): string
    {
        return $this->broadcastName;
    }

    public function getAiringDatetime(): CarbonPeriod
    {
        return $this->datetime;
    }
}
