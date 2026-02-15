<?php

use App\Models\VoiceProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        $filePath = storage_path('app/voice-profiles/kim-komando.md');

        $content = File::exists($filePath)
            ? File::get($filePath)
            : 'Write in the voice of Kim Komando — conversational, direct, warm but urgent, with short punchy sentences and a trusted-friend tone.';

        VoiceProfile::create([
            'name' => 'Kim Komando',
            'content' => $content,
        ]);
    }

    public function down(): void
    {
        VoiceProfile::where('name', 'Kim Komando')->delete();
    }
};
