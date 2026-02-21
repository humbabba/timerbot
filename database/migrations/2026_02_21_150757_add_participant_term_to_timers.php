<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            $table->string('participant_term', 50)->default('speaker')->after('participant_count');
            $table->string('participant_term_plural', 50)->default('speakers')->after('participant_term');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            $table->dropColumn(['participant_term', 'participant_term_plural']);
        });
    }
};
