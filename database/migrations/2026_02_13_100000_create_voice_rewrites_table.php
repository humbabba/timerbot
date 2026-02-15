<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_rewrites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voice_profile_id')->constrained()->cascadeOnDelete();
            $table->longText('original_text');
            $table->longText('rewritten_text');
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('activity_log_id')->nullable();
            $table->timestamps();

            $table->index('voice_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_rewrites');
    }
};
