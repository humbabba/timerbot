<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trash', function (Blueprint $table) {
            $table->id();
            $table->string('trashable_type');
            $table->unsignedBigInteger('trashable_id');
            $table->json('data');
            $table->json('relationships')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deleted_at');
            $table->timestamps();

            $table->index(['trashable_type', 'trashable_id']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trash');
    }
};
