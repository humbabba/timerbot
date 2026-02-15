<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->string('url_source_field')->nullable()->after('max_tokens');
            $table->string('url_target_field')->nullable()->after('url_source_field');
        });
    }

    public function down(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn(['url_source_field', 'url_target_field']);
        });
    }
};
