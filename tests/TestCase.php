<?php

namespace Tests;

use App\DTO\BroadcastAiring;
use App\Models\User;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Spatie\Period\Period;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function authenticate(?User $user = null): void
    {
        Sanctum::actingAs($user ?? User::factory()->create());
    }

    public function makeAirings(int $amount): Collection
    {
        if ($amount < 0) {
            throw new Exception('Amount must be positive');
        }

        $now = now()->toImmutable();
        $iteration = 0;
        $payload = collect();

        while ($iteration < $amount) {
            $payload->push(
                new BroadcastAiring(
                    sprintf('Broadcast #%d', $iteration),
                    Period::make(start: $now, end: $now->addMinutes(30))
                )
            );

            $now->addMinutes(10);
            $iteration++;
        }

        return $payload;
    }
}
