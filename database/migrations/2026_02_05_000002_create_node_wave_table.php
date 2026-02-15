<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('node_wave', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wave_id')->constrained()->cascadeOnDelete();
            $table->foreignId('node_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->json('mappings')->nullable();
            $table->boolean('include_in_output')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('node_wave');
    }
};
