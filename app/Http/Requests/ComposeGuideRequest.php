<?php

namespace App\Http\Requests;

use App\Models\Channel;
use App\Rules\PeriodHasNoOverlap;
use Illuminate\Foundation\Http\FormRequest;

class ComposeGuideRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'channel_number' => ['required', 'int', 'exists:channels,number'],
            'broadcast_name' => ['required', 'string', 'max:100'],
            'starts_at'      => ['bail', 'required', 'date', 'date_format:Y-m-d H:i:s', 'before:ends_at'],
            'ends_at'        => [
                'required',
                'date',
                'date_format:Y-m-d H:i:s',
                new PeriodHasNoOverlap(existingAirings: $this->getChannel()->airingsOn($this->input('starts_at')))
            ],
        ];
    }

    public function getChannel(): Channel
    {
        return Channel::firstWhere('number', $this->input('channel_number'));
    }
}
