<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->string('description')->nullable();
            $table->timestamps();
        });

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

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('loggable_type');
            $table->unsignedBigInteger('loggable_id');
            $table->string('loggable_name')->nullable();
            $table->string('action');
            $table->json('changes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['loggable_type', 'loggable_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('trash');
        Schema::dropIfExists('app_settings');
    }
};
