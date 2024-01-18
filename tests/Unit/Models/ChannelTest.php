<?php

namespace Tests\Unit\Models;

use App\DTO\BroadcastAiring;
use App\Models\Broadcast;
use App\Models\Channel;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ChannelTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function it_successfully_retrieves_associated_broadcasts()
    {
        $channel = Channel::factory()->create();
        $broadcast = Broadcast::factory()->create();

        $channel->broadcasts()->attach(
            $broadcast->id,
            [
                'starts_at' => $startsAt = now()->addMinute(),
                'ends_at'   => $endsAt = now()->addHour(),
            ]
        );

        $airing = $channel->broadcasts()->first();

        $this->assertInstanceOf(Broadcast::class, $airing);
        $this->assertEquals($startsAt, $airing->airing->starts_at);
        $this->assertEquals($endsAt, $airing->airing->ends_at);
    }

    /** @test */
    public function it_successfully_creates_broadcast_on_specified_datetime_with_addBroadcast_method()
    {
        $channel = Channel::factory()->create();

        $airing = $channel->addBroadcast(
            new BroadcastAiring(
                $broadcastName = 'Panorāma',
                CarbonPeriod::create(
                    $startsAt = now()->addMinute(),
                    $endsAt = now()->addHour()
                )
            )
        );

        $this->assertInstanceOf(Broadcast::class, $airing);
        $this->assertEquals($broadcastName, $airing->name);
        $this->assertEquals($startsAt, $airing->airing->starts_at);
        $this->assertEquals($endsAt, $airing->airing->ends_at);
    }
}
