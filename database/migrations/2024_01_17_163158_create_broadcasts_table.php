<?php

use App\Models\Broadcast;
use App\Models\Channel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('name', length: 100)->unique();
        });

        Schema::create('broadcast_channel', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Broadcast::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignIdFor(Channel::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->index();
        });
    }
};
