<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_wave_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wave_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'wave_id']);
        });

        Schema::table('waves', function (Blueprint $table) {
            $table->dropColumn('is_favorite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_wave_favorites');

        Schema::table('waves', function (Blueprint $table) {
            $table->boolean('is_favorite')->default(false)->after('description');
        });
    }
};
