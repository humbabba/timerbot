<?php

use App\Models\AppSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        AppSetting::create([
            'key' => 'news',
            'value' => '',
            'type' => 'richtext',
            'group' => 'general',
            'description' => 'News or announcements to display to users (supports HTML)',
        ]);
    }

    public function down(): void
    {
        AppSetting::where('key', 'news')->delete();
    }
};
