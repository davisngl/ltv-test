<?php

namespace App\DTO;

use App\Contracts\BroadcastAiringInterface;
use Spatie\Period\Period;

readonly class BroadcastAiring implements BroadcastAiringInterface
{
    public function __construct(
        protected string $broadcastName,
        protected Period $datetime
    )
    {
    }

    public function getBroadcastName(): string
    {
        return $this->broadcastName;
    }

    public function getAiringDatetime(): Period
    {
        return $this->datetime;
    }
}
