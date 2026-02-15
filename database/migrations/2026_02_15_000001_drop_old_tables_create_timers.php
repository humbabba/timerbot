<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Disable FK checks so we can drop in any order
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::dropIfExists('node_wave');
        Schema::dropIfExists('user_wave_favorites');
        Schema::dropIfExists('voice_rewrites');
        Schema::dropIfExists('voice_profiles');
        Schema::dropIfExists('nodes');
        Schema::dropIfExists('waves');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create timers table
        Schema::create('timers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('end_time');
            $table->unsignedInteger('participant_count');
            $table->json('warnings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timers');
    }
};
