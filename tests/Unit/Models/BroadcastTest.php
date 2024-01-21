<?php

namespace Tests\Unit\Models;

use App\Models\Broadcast;
use App\Models\Channel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BroadcastTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function it_successfully_retrieves_associated_broadcasts_across_channels()
    {
        $channel = Channel::factory()->create();
        $broadcast = Broadcast::factory()->create();

        $broadcast->channels()->attach(
            $channel->id,
            [
                'starts_at' => $startsAt = now()->addMinute(),
                'ends_at'   => $endsAt = now()->addHour(),
            ]
        );

        $airing = $broadcast->channels()->first();

        $this->assertInstanceOf(Channel::class, $airing);
        $this->assertEquals($startsAt, $airing->airing->starts_at);
        $this->assertEquals($endsAt, $airing->airing->ends_at);
    }
}
