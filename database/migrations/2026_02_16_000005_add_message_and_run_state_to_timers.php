<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            $table->text('message')->nullable()->after('warnings');
            $table->json('run_state')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            $table->dropColumn(['message', 'run_state']);
        });
    }
};
