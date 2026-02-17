<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_admin')->default(false);
            $table->timestamps();

            $table->unique(['group_id', 'user_id']);
        });

        Schema::table('timers', function (Blueprint $table) {
            $table->enum('visibility', ['public', 'private'])->default('public')->after('name');
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete()->after('visibility');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete()->after('group_id');
        });
    }

    public function down(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['visibility', 'group_id', 'created_by']);
        });

        Schema::dropIfExists('group_user');
        Schema::dropIfExists('groups');
    }
};
