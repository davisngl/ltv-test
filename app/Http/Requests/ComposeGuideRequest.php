<?php

namespace App\Http\Requests;

use App\Models\Channel;
use App\Rules\PeriodHasNoOverlap;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;

class ComposeGuideRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'channel_number' => ['required', 'int', 'exists:channels,number'],
            'broadcast_name' => ['required', 'string', 'max:100'],
            'starts_at'      => [
                'required',
                'date_format:Y-m-d H:i:s',
                'before:ends_at',
                new PeriodHasNoOverlap(
                    existingAirings: $this->getChannel()->airingsOn(
                        CarbonImmutable::createFromFormat('Y-m-d H:i:s', $this->input('starts_at'))
                    ),
                    createdAiringPeriod: Period::make(
                        start: $this->input('starts_at'),
                        end: $this->input('ends_at'),
                        precision: Precision::SECOND(),
                        boundaries: Boundaries::EXCLUDE_ALL()
                    )
                )
            ],
            'ends_at'        => ['required', 'date_format:Y-m-d H:i:s'],
        ];
    }

    public function getChannel(): Channel
    {
        return Channel::firstWhere('number', $this->input('channel_number'));
    }
}
