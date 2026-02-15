<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\VoiceProfile;
use App\Models\VoiceRewrite;
use Illuminate\Http\Request;

class VoiceRewriteController extends Controller
{
    public const MAX_REWRITES = 10;

    public function create(VoiceProfile $voiceProfile)
    {
        $voiceProfile->load('rewrites');
        $rewriteCount = $voiceProfile->rewrites->count();
        $atLimit = $rewriteCount >= self::MAX_REWRITES;

        return view('voice-rewrites.create', compact('voiceProfile', 'rewriteCount', 'atLimit'));
    }

    public function store(Request $request, VoiceProfile $voiceProfile)
    {
        if ($voiceProfile->rewrites()->count() >= self::MAX_REWRITES) {
            return redirect()->route('voice-rewrites.create', $voiceProfile)
                ->with('error', 'This voice profile already has the maximum of ' . self::MAX_REWRITES . ' rewrite pairs. Delete one first.');
        }

        $validated = $request->validate([
            'original_text' => 'required|string',
            'rewritten_text' => 'required|string',
            'notes' => 'nullable|string|max:255',
            'activity_log_id' => 'nullable|integer',
        ]);

        $voiceProfile->rewrites()->create($validated);

        return redirect()->route('voice-profiles.show', $voiceProfile)
            ->with('status', 'Rewrite pair added successfully.');
    }

    public function destroyOldest(VoiceProfile $voiceProfile)
    {
        $oldest = $voiceProfile->rewrites()->oldest()->first();

        if ($oldest) {
            $oldest->delete();
        }

        return redirect()->route('voice-rewrites.create', $voiceProfile)
            ->with('status', 'Oldest rewrite pair deleted. You can now add a new one.');
    }

    public function edit(VoiceProfile $voiceProfile, VoiceRewrite $rewrite)
    {
        return view('voice-rewrites.edit', compact('voiceProfile', 'rewrite'));
    }

    public function update(Request $request, VoiceProfile $voiceProfile, VoiceRewrite $rewrite)
    {
        $validated = $request->validate([
            'original_text' => 'required|string',
            'rewritten_text' => 'required|string',
            'notes' => 'nullable|string|max:255',
        ]);

        $rewrite->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Rewrite updated successfully.']);
        }

        return redirect()->route('voice-profiles.show', $voiceProfile)
            ->with('status', 'Rewrite updated successfully.');
    }

    public function destroy(Request $request, VoiceProfile $voiceProfile, VoiceRewrite $rewrite)
    {
        $rewrite->delete();

        if ($request->input('redirect_to_create')) {
            return redirect()->route('voice-rewrites.create', $voiceProfile)
                ->with('status', 'Rewrite pair deleted. You can now add a new one.');
        }

        return redirect()->route('voice-profiles.show', $voiceProfile)
            ->with('status', 'Rewrite deleted successfully.');
    }

    public function compare(Request $request, VoiceProfile $voiceProfile)
    {
        $validated = $request->validate([
            'original_text' => 'required|string',
            'rewritten_text' => 'required|string',
        ]);

        $cleanOriginal = $this->stripMarkup($validated['original_text']);
        $cleanRewritten = $this->stripMarkup($validated['rewritten_text']);

        $originalWords = preg_split('/\s+/', $cleanOriginal, -1, PREG_SPLIT_NO_EMPTY);
        $rewrittenWords = preg_split('/\s+/', $cleanRewritten, -1, PREG_SPLIT_NO_EMPTY);

        [$removedWords, $addedWords, $unchangedCount] = $this->computeDiff($originalWords, $rewrittenWords);

        $totalWords = max(count($originalWords), count($rewrittenWords), 1);
        $changedWords = count($removedWords) + count($addedWords);
        $percentage = min(100, round(($changedWords / ($totalWords + $changedWords)) * 100));

        $notes = $this->generateNotes($cleanOriginal, $cleanRewritten, $removedWords, $addedWords);

        return response()->json([
            'percentage' => $percentage,
            'notes' => $notes,
        ]);
    }

    private function stripMarkup(string $text): string
    {
        // Strip HTML tags
        $text = strip_tags($text);
        // Strip markdown images ![alt](url)
        $text = preg_replace('/!\[[^\]]*\]\([^)]+\)/', '', $text);
        // Strip markdown links [text](url) → keep text
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text);
        // Strip bold/italic markers
        $text = preg_replace('/[*_]{1,3}/', '', $text);
        // Strip inline code
        $text = preg_replace('/`[^`]+`/', '', $text);
        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));

        return $text;
    }

    private function computeDiff(array $original, array $rewritten): array
    {
        $m = count($original);
        $n = count($rewritten);

        // Build LCS table
        $lcs = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));
        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if (mb_strtolower($original[$i - 1]) === mb_strtolower($rewritten[$j - 1])) {
                    $lcs[$i][$j] = $lcs[$i - 1][$j - 1] + 1;
                } else {
                    $lcs[$i][$j] = max($lcs[$i - 1][$j], $lcs[$i][$j - 1]);
                }
            }
        }

        // Backtrack to find removed and added words
        $removed = [];
        $added = [];
        $unchangedCount = $lcs[$m][$n];
        $i = $m;
        $j = $n;

        while ($i > 0 && $j > 0) {
            if (mb_strtolower($original[$i - 1]) === mb_strtolower($rewritten[$j - 1])) {
                $i--;
                $j--;
            } elseif ($lcs[$i - 1][$j] >= $lcs[$i][$j - 1]) {
                $removed[] = $original[$i - 1];
                $i--;
            } else {
                $added[] = $rewritten[$j - 1];
                $j--;
            }
        }

        while ($i > 0) {
            $removed[] = $original[$i - 1];
            $i--;
        }
        while ($j > 0) {
            $added[] = $rewritten[$j - 1];
            $j--;
        }

        return [$removed, $added, $unchangedCount];
    }

    private function generateNotes(string $original, string $rewritten, array $removedWords, array $addedWords): array
    {
        $notes = [];

        $origWords = preg_split('/\s+/', $original, -1, PREG_SPLIT_NO_EMPTY);
        $rewriteWords = preg_split('/\s+/', $rewritten, -1, PREG_SPLIT_NO_EMPTY);
        $origCount = count($origWords);
        $rewriteCount = count($rewriteWords);

        // Word count change
        $diff = $origCount - $rewriteCount;
        if ($diff > 0) {
            $notes[] = "Shortened by {$diff} word" . ($diff !== 1 ? 's' : '');
        } elseif ($diff < 0) {
            $absDiff = abs($diff);
            $notes[] = "Expanded by {$absDiff} word" . ($absDiff !== 1 ? 's' : '');
        }

        // Sentence count change
        $origSentences = preg_split('/[.!?]+/', $original, -1, PREG_SPLIT_NO_EMPTY);
        $rewriteSentences = preg_split('/[.!?]+/', $rewritten, -1, PREG_SPLIT_NO_EMPTY);
        $origSentenceCount = count(array_filter($origSentences, fn($s) => trim($s) !== ''));
        $rewriteSentenceCount = count(array_filter($rewriteSentences, fn($s) => trim($s) !== ''));

        if ($origSentenceCount !== $rewriteSentenceCount) {
            if ($rewriteSentenceCount < $origSentenceCount) {
                $notes[] = "Condensed from {$origSentenceCount} to {$rewriteSentenceCount} sentences";
            } else {
                $notes[] = "Expanded from {$origSentenceCount} to {$rewriteSentenceCount} sentences";
            }
        }

        // Punctuation changes
        $origExcl = substr_count($original, '!');
        $rewriteExcl = substr_count($rewritten, '!');
        if ($rewriteExcl > $origExcl) {
            $notes[] = 'Added exclamation marks';
        } elseif ($rewriteExcl < $origExcl) {
            $notes[] = 'Removed exclamation marks';
        }

        $origQuestion = substr_count($original, '?');
        $rewriteQuestion = substr_count($rewritten, '?');
        if ($rewriteQuestion > $origQuestion) {
            $notes[] = 'Added question marks';
        } elseif ($rewriteQuestion < $origQuestion) {
            $notes[] = 'Removed question marks';
        }

        // Average word length change (proxy for vocabulary complexity)
        if ($origCount > 0 && $rewriteCount > 0) {
            $origAvg = array_sum(array_map('mb_strlen', $origWords)) / $origCount;
            $rewriteAvg = array_sum(array_map('mb_strlen', $rewriteWords)) / $rewriteCount;
            $avgDiff = $rewriteAvg - $origAvg;

            if ($avgDiff < -0.5) {
                $notes[] = 'Simplified vocabulary (shorter words on average)';
            } elseif ($avgDiff > 0.5) {
                $notes[] = 'More complex vocabulary (longer words on average)';
            }
        }

        if (empty($notes)) {
            $notes[] = 'Minimal structural changes detected';
        }

        return $notes;
    }

    public function waveOutputs(Request $request)
    {
        $query = ActivityLog::where('loggable_type', \App\Models\Wave::class)
            ->where('action', 'run')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('loggable_name', 'like', '%' . $request->search . '%')
                  ->orWhere('changes->node', 'like', '%' . $request->search . '%');
            });
        }

        $logs = $query->paginate(15);

        return response()->json([
            'data' => $logs->map(function ($log) {
                $changes = $log->changes ?? [];
                return [
                    'id' => $log->id,
                    'wave_name' => $log->loggable_name,
                    'node_name' => $changes['node'] ?? '',
                    'step' => $changes['step'] ?? '',
                    'output' => $changes['output'] ?? '',
                    'created_at' => $log->created_at->format('M j, Y g:i A'),
                ];
            }),
            'next_page_url' => $logs->nextPageUrl(),
            'prev_page_url' => $logs->previousPageUrl(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
        ]);
    }
}
