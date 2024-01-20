<?php

namespace App\Http\Requests;

use App\Models\Channel;
use Illuminate\Foundation\Http\FormRequest;

class ComposeGuideRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'channel_number' => ['required', 'int', 'exists:channels,number'],
            'broadcast_name' => ['required', 'string', 'max:100'],
            'starts_at'      => ['required', 'date_format:Y-m-d H:i:s'],
            'ends_at'        => ['required', 'date_format:Y-m-d H:i:s'],
        ];
    }

    public function getChannel(): Channel
    {
        return Channel::firstWhere('number', $this->input('channel_number'));
    }
}
