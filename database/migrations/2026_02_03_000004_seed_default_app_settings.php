<?php

use App\Models\AppSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        AppSetting::create([
            'key' => 'trash_retention_days',
            'value' => '30',
            'type' => 'integer',
            'group' => 'trash',
            'description' => 'Number of days to retain items in trash before automatic cleanup',
        ]);

        AppSetting::create([
            'key' => 'trash_auto_cleanup_enabled',
            'value' => 'true',
            'type' => 'boolean',
            'group' => 'trash',
            'description' => 'Enable automatic cleanup of old trash items',
        ]);
    }

    public function down(): void
    {
        AppSetting::whereIn('key', ['trash_retention_days', 'trash_auto_cleanup_enabled'])->delete();
    }
};
