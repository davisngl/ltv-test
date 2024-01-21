<?php

namespace Tests\Feature;

use App\DTO\BroadcastAiring;
use App\Exceptions\DateFilterException;
use App\Models\Channel;
use Generator;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Fluent;
use Illuminate\Testing\Fluent\AssertableJson;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;
use Tests\TestCase;

class GuideControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function formatTimestamps(array $payload): array
    {
        $fluent = new Fluent($payload);

        // Due to formatting requirements, we need to pass timestamps in according format.
        $fluent->starts_at = $fluent->starts_at->format('Y-m-d H:i:s');
        $fluent->ends_at = $fluent->ends_at->format('Y-m-d H:i:s');

        return $fluent->toArray();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->authenticate();
    }

    /** @test */
    public function it_throws_an_exception_if_incorrect_date_format_is_supplied_for_getting_TV_guide()
    {
        $channel = Channel::factory()->create();

        $this
            ->withoutExceptionHandling()
            ->assertThrows(
                fn () => $this->getJson(route('guide-for-day', ['channel' => $channel, 'date' => ':invalid:'])),
                DateFilterException::class
            );
    }

    /** @test */
    public function it_returns_a_guide_for_the_day_successfully()
    {
        $channel = Channel::factory()->create();

        $this->makeAirings($amountOfAirings = 10)->map(
            static fn (BroadcastAiring $airing) => $channel->addBroadcast($airing)
        );

        $this
            ->getJson(
                route(
                    'guide-for-day',
                    ['channel' => $channel->number, 'date' => $date = now()->format('Y-m-d')]
                )
            )
            ->assertOk()
            ->assertJson(static fn (AssertableJson $json) => $json
                ->where('message', 'Guide for the day retrieved successfully')
                ->has('data', $amountOfAirings)
                ->etc()
            );

        $this->assertCount($amountOfAirings, $channel->broadcasts);
        $this->assertDatabaseCount('broadcasts', 10);
        $this->assertDatabaseCount('broadcast_channel', 10);
    }

    /**
     * @test
     *
     * @dataProvider invalidDataProvider
     */
    public function it_fails_validation_when_providing_invalid_data_for_adding_airings(array $payload, array $keys, ?callable $setup = null)
    {
        Channel::factory()->create(['number' => 1]);

        if ($setup) {
            $setup();
        }

        $payload = $this->formatTimestamps($payload);

        $this
            ->postJson(route('compose-guide'), $payload)
            ->assertJsonValidationErrors($keys);
    }

    public static function invalidDataProvider(): Generator
    {
        $payload = [
            'channel_number' => 1,
            'broadcast_name' => ':broadcast:',
            'starts_at'      => $now = now()->setTime(7, 0)->toImmutable(),
            'ends_at'        => $now->addMinutes(30),
        ];

        yield from [
            'broadcast_name too long' => [
                'payload' => array_merge($payload, ['broadcast_name' => str('s')->repeat(101)]),
                'keys'    => ['broadcast_name'],
            ],
            'starts_at is not before ends_at datetime' => [
                'payload' => array_merge($payload, ['ends_at' => $payload['starts_at']->subDay()]),
                'keys'    => ['starts_at'],
            ],
            'airing at given time already exists' => [
                'payload' => $payload,
                'keys'    => ['ends_at'],
                'setup'   => static function () use ($payload) {
                    $airingPayload = new BroadcastAiring(
                        'Broadcast Test',
                        Period::make(
                            start: $payload['starts_at'],
                            end: $payload['ends_at'],
                            precision: Precision::SECOND(),
                            boundaries: Boundaries::EXCLUDE_ALL()
                        )
                    );

                    Channel::query()
                        ->firstWhere('number', $payload['channel_number'])
                        ->addBroadcast($airingPayload);
                },
            ],
            // ...and many more
        ];
    }

    /** @test */
    public function it_returns_not_found_response_if_there_is_no_current_broadcast()
    {
        $channel = Channel::factory()->create();

        $this
            ->getJson(route('on-air', ['channel' => $channel->number]))
            ->assertNotFound();
    }

    /** @test */
    public function it_successfully_returns_upcoming_broadcasts()
    {
        $channel = Channel::factory()->create();
        // Travel to later time to create "upcoming" broadcasts
        $this->travelTo(now()->setTime(10, 0));

        $this->makeAirings(10)->map(
            static fn (BroadcastAiring $airing) => $channel->addBroadcast($airing)
        );

        // Travel back in order to have all those broadcasts as upcoming ones
        $this->travelTo(now()->setTime(6, 0));

        $this
            ->getJson(route('upcoming-broadcasts', ['channel' => $channel->number]))
            ->assertOk()
            ->assertJson(static fn (AssertableJson $json) => $json->has('data', 10)->etc());
    }
}
