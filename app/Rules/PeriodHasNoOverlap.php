<?php

namespace App\Rules;

use App\Models\Broadcast;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;

readonly class PeriodHasNoOverlap implements ValidationRule
{
    public function __construct(
        private Collection $existingAirings,
        private Period $createdAiringPeriod
    ) {}

    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /**
         * We should check overlap for all existing airings and the one we are making.
         * If there were any overlaps, it would make some airings in API response shorter
         * (airing X 'ends_at' bleeds into next airing Y 'starts_at' time, fixing it would involve shortening the airing).
         */
        $hasOverlappingPeriod = $this->existingAirings
            ->map(function (Broadcast $broadcast) {
                $broadcast->period = Period::make(
                    start: $broadcast->airing->starts_at,
                    end: $broadcast->airing->ends_at,
                    precision: Precision::SECOND(),
                    /**
                     * This allows to put an airing (starts_at: 2024-01-20 20:00:00)
                     * next to existing airing (ends_at: 2024-01-20 20:00:00),
                     * but not a single second overlapping.
                     */
                    boundaries: Boundaries::EXCLUDE_ALL()
                );

                return $broadcast;
            })
            ->first(fn(Broadcast $broadcast) => $this->createdAiringPeriod->overlapsWith($broadcast->period));

        if ($hasOverlappingPeriod) {
            $fail('Given starts_at and ends_at overlaps with existing broadcast airing');
        }
    }
}
