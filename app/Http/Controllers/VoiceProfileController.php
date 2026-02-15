<?php

namespace App\Http\Controllers;

use App\Models\VoiceProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VoiceProfileController extends Controller
{
    public function index(Request $request)
    {
        $query = VoiceProfile::query()->latest();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $voiceProfiles = $query->paginate(20)->withQueryString();

        return view('voice-profiles.index', compact('voiceProfiles'));
    }

    public function create()
    {
        return view('voice-profiles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        VoiceProfile::create($validated);

        return redirect()->route('voice-profiles.index')->with('status', 'Voice profile created successfully.');
    }

    public function show(VoiceProfile $voiceProfile)
    {
        $voiceProfile->load('rewrites');

        return view('voice-profiles.show', compact('voiceProfile'));
    }

    public function edit(VoiceProfile $voiceProfile)
    {
        return view('voice-profiles.edit', compact('voiceProfile'));
    }

    public function update(Request $request, VoiceProfile $voiceProfile)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $voiceProfile->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Voice profile updated successfully.']);
        }

        return redirect()->route('voice-profiles.index')->with('status', 'Voice profile updated successfully.');
    }

    public function destroy(VoiceProfile $voiceProfile)
    {
        $voiceProfile->trash();

        return redirect()->route('voice-profiles.index')->with('status', 'Voice profile moved to trash.');
    }

    public function refine(VoiceProfile $voiceProfile)
    {
        $voiceProfile->load('rewrites');

        if ($voiceProfile->rewrites->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'No rewrite pairs exist to learn from.'], 422);
        }

        $rewriteBlock = $this->buildRewriteBlock($voiceProfile->rewrites);

        $systemPrompt = "You are an expert at analyzing writing voice and style. Your task is to study before/after rewrite examples and produce improved voice profile instructions that fully capture the demonstrated patterns.\n\nOutput ONLY the improved voice profile instructions text. Do not include any preamble, explanation, or commentary.";

        $userMessage = "Here are the current voice profile instructions:\n\n---\n" . $voiceProfile->content . "\n---\n\n" . $rewriteBlock . "\n\nAnalyze the rewrite examples above carefully. Identify voice patterns, stylistic choices, tone, and structural preferences demonstrated in the rewrites. Compare what the current instructions capture vs. what the examples demonstrate.\n\nProduce an improved version of the voice profile instructions that more completely and accurately captures the voice shown in the rewrite examples. Keep a similar format and level of detail to the original instructions, but refine and expand them based on what the examples reveal.";

        $response = Http::timeout(120)->withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model'),
            'max_tokens' => 4096,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        if ($response->failed()) {
            return response()->json(['success' => false, 'error' => 'API request failed.'], 500);
        }

        $data = $response->json();
        $refinedContent = $data['content'][0]['text'] ?? null;

        if (!$refinedContent) {
            return response()->json(['success' => false, 'error' => 'No content returned from API.'], 500);
        }

        return response()->json(['success' => true, 'refined_content' => $refinedContent]);
    }

    protected function buildRewriteBlock($rewrites): string
    {
        $block = "=== VOICE REWRITE EXAMPLES ===\n";
        $block .= "Study these before/after examples carefully. They show how the original AI output\n";
        $block .= "was rewritten to match the desired voice. Apply the same patterns to your output.\n\n";

        foreach ($rewrites as $i => $rewrite) {
            $block .= '--- Example ' . ($i + 1) . " ---\n";
            if ($rewrite->notes) {
                $block .= '[' . $rewrite->notes . "]\n\n";
            }
            $block .= "ORIGINAL:\n" . $rewrite->original_text . "\n\n";
            $block .= "REWRITTEN:\n" . $rewrite->rewritten_text . "\n\n";
        }

        $block .= '=== END REWRITE EXAMPLES ===';

        return $block;
    }
}
