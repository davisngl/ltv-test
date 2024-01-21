<?php

namespace Database\Seeders;

use App\Models\Broadcast;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create();

        Channel::factory(3)
            ->state(
                new Sequence(
                    ['number' => 1],
                    ['number' => 2],
                    ['number' => 3],
                )
            )
            ->create();

        $dummyBroadcasts = File::json(database_path('seeders/guide/broadcasts.json'));

        collect($dummyBroadcasts)->each(fn (array $broadcast) => Broadcast::firstOrCreate(['name' => $broadcast['name']])
        );

        $this->command->info(
            vsprintf('%d broadcasts have been seeded for initial usage.', [count($dummyBroadcasts)])
        );

        $token = $user->createToken('api')->plainTextToken;

        $this->command->info("API token created: $token");
    }
}
