<?php

namespace App\Http\Resources;

use App\Models\Broadcast;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Broadcast */
class BroadcastResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'broadcast_id' => $this->id,
            'name'         => $this->name,
            'starts_at'    => $this->airing->starts_at,
            'ends_at'      => $this->airing->ends_at,
        ];
    }
}
