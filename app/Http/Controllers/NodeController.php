<?php

namespace App\Http\Controllers;

use App\Helpers\TextFilter;
use App\Models\ActivityLog;
use App\Models\Node;
use App\Models\VoiceProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NodeController extends Controller
{
    /**
     * Check if system text contains image generation keywords.
     */
    protected function requiresImageGeneration(?string $systemText): bool
    {
        if (empty($systemText)) {
            return false;
        }

        $keywords = [
            'generate image', 'generate an image',
            'create image', 'create an image',
            'draw image', 'draw an image',
            'make image', 'make an image',
            'produce image', 'produce an image',
        ];

        $lowerText = strtolower($systemText);
        foreach ($keywords as $keyword) {
            if (str_contains($lowerText, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine image size based on keywords in system text.
     * Returns: 1792x1024 (landscape), 1024x1792 (portrait), or 1024x1024 (square)
     */
    protected function resolveImageModel(array $inputs): string
    {
        $allowed = [
            'gpt-image-1', 'dall-e-3',
            'imagen-4.0-generate-001', 'imagen-4.0-fast-generate-001', 'imagen-4.0-ultra-generate-001',
        ];
        $value = strtolower(trim($inputs['model'] ?? ''));

        if (in_array($value, $allowed)) {
            return $value;
        }

        return config('services.openai.model');
    }

    protected function isGeminiModel(string $model): bool
    {
        return str_starts_with($model, 'imagen-');
    }

    protected function getImageSize(?string $systemText, string $model = null): string
    {
        $model = $model ?? config('services.openai.model');

        if ($this->isGeminiModel($model)) {
            if (empty($systemText)) {
                return '16:9';
            }

            $lowerText = strtolower($systemText);

            $squareKeywords = ['square image', 'square format', '1:1', '1x1'];
            foreach ($squareKeywords as $keyword) {
                if (str_contains($lowerText, $keyword)) {
                    return '1:1';
                }
            }

            $portraitKeywords = ['portrait image', 'portrait format', 'vertical image', 'tall image'];
            foreach ($portraitKeywords as $keyword) {
                if (str_contains($lowerText, $keyword)) {
                    return '9:16';
                }
            }

            return '16:9';
        }

        $isDalle = str_starts_with($model, 'dall-e');

        if (empty($systemText)) {
            return $isDalle ? '1792x1024' : '1536x1024';
        }

        $lowerText = strtolower($systemText);

        // Check for square keywords
        $squareKeywords = ['square image', 'square format', '1:1', '1x1'];
        foreach ($squareKeywords as $keyword) {
            if (str_contains($lowerText, $keyword)) {
                return '1024x1024';
            }
        }

        // Check for portrait keywords
        $portraitKeywords = ['portrait image', 'portrait format', 'vertical image', 'tall image'];
        foreach ($portraitKeywords as $keyword) {
            if (str_contains($lowerText, $keyword)) {
                return $isDalle ? '1024x1792' : '1024x1536';
            }
        }

        // Default to landscape
        return $isDalle ? '1792x1024' : '1536x1024';
    }

    /**
     * Validate that any {field} references in system_text match actual input field names.
     * Escaped braces like \{literal\} are ignored.
     *
     * @return array List of unmatched field references, empty if all valid
     */
    protected function validateFieldReferences(?string $systemText, array $inputs): array
    {
        if (empty($systemText)) {
            return [];
        }

        // Extract {field} references, ignoring escaped \{...\}
        // Uses negative lookbehind to skip braces preceded by backslash
        preg_match_all('/(?<!\\\\)\{([^}]+)\}/', $systemText, $matches);

        if (empty($matches[1])) {
            return [];
        }

        // Get input field names (case-insensitive comparison)
        $fieldNames = collect($inputs)->pluck('name')->map(fn($name) => strtolower($name))->toArray();

        // Find any references that don't match a field name
        $unmatched = [];
        foreach ($matches[1] as $reference) {
            if (!in_array(strtolower($reference), $fieldNames)) {
                $unmatched[] = $reference;
            }
        }

        return array_unique($unmatched);
    }

    public function index(Request $request)
    {
        $query = Node::orderBy('name');

        if ($request->filled('search')) {
            $query->search($request->search, ['name']);
        }

        if ($request->filled('from') || $request->filled('to')) {
            $query->createdBetween($request->from, $request->to);
        }

        $nodes = $query->paginate(20)->withQueryString();

        return view('nodes.index', compact('nodes'));
    }

    public function create()
    {
        $voiceProfiles = VoiceProfile::orderBy('name')->get();

        return view('nodes.create', compact('voiceProfiles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'inputs' => 'nullable|array',
            'inputs.*.name' => 'required|string|max:255',
            'inputs.*.type' => 'required|string|in:text,textarea,number,email,select,checkbox,radio,file',
            'inputs.*.label' => 'required|string|max:255',
            'inputs.*.required' => 'boolean',
            'inputs.*.options' => 'nullable|string',
            'inputs.*.default' => 'nullable|string',
            'system_text' => 'nullable|string',
            'max_tokens' => 'required|integer|min:1|max:65536',
            'url_source_field' => 'nullable|string|max:255',
            'url_target_field' => 'nullable|string|max:255',
            'voice_profile_id' => 'nullable|exists:voice_profiles,id',
            'style_check' => 'boolean',
        ]);

        // Validate {field} references in system_text match actual input names
        $unmatchedRefs = $this->validateFieldReferences(
            $validated['system_text'] ?? null,
            $validated['inputs'] ?? []
        );

        if (!empty($unmatchedRefs)) {
            return back()
                ->withInput()
                ->withErrors(['system_text' => 'System text references undefined fields: {' . implode('}, {', $unmatchedRefs) . '}']);
        }

        Node::create([
            'name' => $validated['name'],
            'inputs' => $validated['inputs'] ?? [],
            'system_text' => $validated['system_text'] ?? null,
            'max_tokens' => $validated['max_tokens'],
            'url_source_field' => $validated['url_source_field'] ?? null,
            'url_target_field' => $validated['url_target_field'] ?? null,
            'voice_profile_id' => $validated['voice_profile_id'] ?? null,
            'style_check' => $request->boolean('style_check'),
        ]);

        return redirect()->route('nodes.index')->with('status', 'Node created successfully.');
    }

    public function show(Node $node)
    {
        return view('nodes.show', compact('node'));
    }

    public function run(Request $request, Node $node)
    {
        // Build validation rules dynamically from node inputs
        $rules = [];
        foreach ($node->inputs ?? [] as $input) {
            $rule = [];
            if (!empty($input['required'])) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }

            match ($input['type']) {
                'email' => $rule[] = 'email',
                'number' => $rule[] = 'numeric',
                'file' => array_push($rule, 'file', 'image', 'max:10240'),
                default => $rule[] = 'string',
            };

            $rules['inputs.' . $input['name']] = $rule;
        }

        $validated = $request->validate($rules);
        $userInputs = $validated['inputs'] ?? [];

        // Build user message content from inputs, handling file uploads as vision blocks
        $hasFiles = false;
        $contentBlocks = [];
        $textParts = [];

        foreach ($node->inputs ?? [] as $input) {
            if ($input['type'] === 'file') {
                $file = $request->file('inputs.' . $input['name']);
                if ($file) {
                    $hasFiles = true;
                    $mimeType = $file->getMimeType();
                    $base64 = base64_encode(file_get_contents($file->getRealPath()));
                    $contentBlocks[] = [
                        'type' => 'image',
                        'source' => [
                            'type' => 'base64',
                            'media_type' => $mimeType,
                            'data' => $base64,
                        ],
                    ];
                }
                continue;
            }

            $value = $userInputs[$input['name']] ?? '';
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $textParts[] = "{$input['label']}: {$value}";
        }

        // Build content: array of blocks if files present, plain string otherwise
        if ($hasFiles) {
            if (!empty($textParts)) {
                $contentBlocks[] = [
                    'type' => 'text',
                    'text' => implode("\n", $textParts),
                ];
            }
            $content = $contentBlocks;
        } else {
            $content = implode("\n", $textParts);
        }

        // Build system prompt, prepending voice profile if assigned
        $systemBlocks = [];
        if ($node->voice_profile_id && $node->voiceProfile) {
            $systemBlocks[] = [
                'type' => 'text',
                'text' => $node->voiceProfile->content,
                'cache_control' => ['type' => 'ephemeral'],
            ];
        }
        $systemBlocks[] = [
            'type' => 'text',
            'text' => $node->system_text ?? '',
            'cache_control' => ['type' => 'ephemeral'],
        ];

        // Execute Claude API query
        $apiPayload = [
            'model' => config('services.anthropic.model'),
            'max_tokens' => $node->max_tokens,
            'system' => $systemBlocks,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $content,
                ],
            ],
        ];

        $debugPrompt = null;
        if (app()->environment('local')) {
            $debugPrompt = [
                'api' => 'Anthropic',
                'endpoint' => 'https://api.anthropic.com/v1/messages',
                'payload' => $apiPayload,
            ];
        }

        $response = Http::timeout(120)->withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', $apiPayload);

        if ($response->successful()) {
            $data = $response->json();
            $result = $data['content'][0]['text'] ?? 'No response content';
            $result = TextFilter::filterProfanity($result);
            $htmlResult = Str::markdown($result, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);

            // Sanitize file inputs for logging
            $logInputs = $userInputs;
            foreach ($node->inputs ?? [] as $input) {
                if ($input['type'] === 'file') {
                    $file = $request->file('inputs.' . $input['name']);
                    $logInputs[$input['name']] = $file ? '[uploaded: ' . $file->getClientOriginalName() . ']' : null;
                }
            }

            $this->logRun($node, $logInputs, $result);

            if ($request->ajax() || $request->wantsJson()) {
                $responseData = [
                    'success' => true,
                    'result' => $htmlResult,
                ];
                if ($debugPrompt) {
                    $responseData['debug_prompt'] = $debugPrompt;
                }
                return response()->json($responseData);
            }

            return view('nodes.show', [
                'node' => $node,
                'result' => $htmlResult,
                'previousInputs' => $userInputs,
            ]);
        }

        $error = "API Error: " . $response->status() . " - " . $response->body();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $error,
            ], 422);
        }

        return view('nodes.show', [
            'node' => $node,
            'result' => $error,
            'previousInputs' => $userInputs,
        ]);
    }

    public function edit(Node $node)
    {
        $voiceProfiles = VoiceProfile::orderBy('name')->get();

        return view('nodes.edit', compact('node', 'voiceProfiles'));
    }

    public function update(Request $request, Node $node)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'inputs' => 'nullable|array',
            'inputs.*.name' => 'required|string|max:255',
            'inputs.*.type' => 'required|string|in:text,textarea,number,email,select,checkbox,radio,file',
            'inputs.*.label' => 'required|string|max:255',
            'inputs.*.required' => 'boolean',
            'inputs.*.options' => 'nullable|string',
            'inputs.*.default' => 'nullable|string',
            'system_text' => 'nullable|string',
            'max_tokens' => 'required|integer|min:1|max:65536',
            'url_source_field' => 'nullable|string|max:255',
            'url_target_field' => 'nullable|string|max:255',
            'voice_profile_id' => 'nullable|exists:voice_profiles,id',
            'style_check' => 'boolean',
        ]);

        // Validate {field} references in system_text match actual input names
        $unmatchedRefs = $this->validateFieldReferences(
            $validated['system_text'] ?? null,
            $validated['inputs'] ?? []
        );

        if (!empty($unmatchedRefs)) {
            $errorMessage = 'System text references undefined fields: {' . implode('}, {', $unmatchedRefs) . '}';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => ['system_text' => [$errorMessage]]
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['system_text' => $errorMessage]);
        }

        $node->update([
            'name' => $validated['name'],
            'inputs' => $validated['inputs'] ?? [],
            'system_text' => $validated['system_text'] ?? null,
            'max_tokens' => $validated['max_tokens'],
            'url_source_field' => $validated['url_source_field'] ?? null,
            'url_target_field' => $validated['url_target_field'] ?? null,
            'voice_profile_id' => $validated['voice_profile_id'] ?? null,
            'style_check' => $request->boolean('style_check'),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Node updated successfully.']);
        }

        return redirect()->route('nodes.index')->with('status', 'Node updated successfully.');
    }

    public function fetchUrl(Request $request, Node $node)
    {
        $validated = $request->validate([
            'url' => 'required|url',
        ]);

        try {
            $response = Http::timeout(30)->get($validated['url']);

            if ($response->successful()) {
                $html = $response->body();
                $text = $this->extractReadableText($html);

                return response()->json([
                    'success' => true,
                    'content' => $text,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch URL: ' . $response->status(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error fetching URL: ' . $e->getMessage(),
            ], 422);
        }
    }

    protected function extractReadableText(string $html): string
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Remove unwanted elements globally first
        $tagsToRemove = ['script', 'style', 'nav', 'footer', 'header', 'aside', 'noscript', 'iframe', 'form', 'button', 'svg', 'figure', 'figcaption'];
        foreach ($tagsToRemove as $tag) {
            $elements = $dom->getElementsByTagName($tag);
            while ($elements->length > 0) {
                $elements->item(0)->parentNode->removeChild($elements->item(0));
            }
        }

        // Remove elements with common "related/sidebar" class patterns
        $xpath = new \DOMXPath($dom);
        $patterns = [
            '//*[contains(@class, "related")]',
            '//*[contains(@class, "sidebar")]',
            '//*[contains(@class, "widget")]',
            '//*[contains(@class, "share")]',
            '//*[contains(@class, "social")]',
            '//*[contains(@class, "comment")]',
            '//*[contains(@class, "newsletter")]',
            '//*[contains(@class, "advert")]',
            '//*[contains(@class, "promo")]',
        ];
        foreach ($patterns as $pattern) {
            $nodes = $xpath->query($pattern);
            foreach ($nodes as $node) {
                if ($node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        // Try to find main content area in order of preference
        $content = null;
        $contentQueries = [
            '//article',
            '//main',
            '//*[@role="main"]',
            '//*[contains(@class, "article-content")]',
            '//*[contains(@class, "article-body")]',
            '//*[contains(@class, "post-content")]',
            '//*[contains(@class, "entry-content")]',
            '//*[contains(@class, "content-body")]',
            '//*[contains(@id, "article")]',
            '//*[contains(@id, "content")]',
        ];

        foreach ($contentQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                $content = $nodes->item(0);
                break;
            }
        }

        // Fall back to body
        if (!$content) {
            $bodies = $dom->getElementsByTagName('body');
            $content = $bodies->length > 0 ? $bodies->item(0) : $dom;
        }

        // Extract text from paragraphs
        $result = '';
        $paragraphs = $content->getElementsByTagName('p');
        foreach ($paragraphs as $p) {
            $text = trim($p->textContent);
            if (strlen($text) > 30) { // Skip very short paragraphs (likely navigation/labels)
                $result .= $text . "\n\n";
            }
        }

        // Clean up
        $result = preg_replace('/  +/', ' ', $result);
        $result = trim($result);

        libxml_clear_errors();

        return $result;
    }

    public function generateImage(Request $request, Node $node)
    {
        // Build validation rules dynamically from node inputs
        $rules = [];
        foreach ($node->inputs ?? [] as $input) {
            $rule = [];
            if (!empty($input['required'])) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }

            match ($input['type']) {
                'email' => $rule[] = 'email',
                'number' => $rule[] = 'numeric',
                'file' => array_push($rule, 'file', 'image', 'max:10240'),
                default => $rule[] = 'string',
            };

            $rules['inputs.' . $input['name']] = $rule;
        }

        $validated = $request->validate($rules);
        $userInputs = $validated['inputs'] ?? [];

        // Find uploaded file (first file input with data)
        $uploadedFile = null;
        foreach ($node->inputs ?? [] as $input) {
            if ($input['type'] === 'file') {
                $file = $request->file('inputs.' . $input['name']);
                if ($file) {
                    $uploadedFile = $file;
                    break;
                }
            }
        }

        // Build prompt from system_text and user inputs
        $prompt = $node->system_text ?? '';

        // Replace {field} placeholders with actual values (skip file inputs)
        foreach ($node->inputs ?? [] as $input) {
            if ($input['type'] === 'file') {
                $prompt = str_ireplace('{' . $input['name'] . '}', '', $prompt);
                continue;
            }
            $value = $userInputs[$input['name']] ?? '';
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $prompt = str_ireplace('{' . $input['name'] . '}', $value, $prompt);
        }

        // If no placeholders, append user inputs to the prompt (skip file inputs)
        if ($prompt === ($node->system_text ?? '')) {
            $contentParts = [];
            foreach ($node->inputs ?? [] as $input) {
                if ($input['type'] === 'file') {
                    continue;
                }
                $value = $userInputs[$input['name']] ?? '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                if (!empty($value)) {
                    $contentParts[] = $value;
                }
            }
            if (!empty($contentParts)) {
                $prompt .= ' ' . implode(', ', $contentParts);
            }
        }

        // Resolve image model (allow override via input named "model")
        $imageModel = $this->resolveImageModel($userInputs);
        $imageSize = $this->getImageSize($prompt, $imageModel);

        // Sanitize file inputs for logging
        $logInputs = $userInputs;
        foreach ($node->inputs ?? [] as $input) {
            if ($input['type'] === 'file') {
                $file = $request->file('inputs.' . $input['name']);
                $logInputs[$input['name']] = $file ? '[uploaded: ' . $file->getClientOriginalName() . ']' : null;
            }
        }

        $debugPrompt = null;

        // If a file was uploaded, use the edits endpoint (gpt-image-1 only)
        if ($uploadedFile) {
            if ($imageModel !== 'gpt-image-1') {
                return response()->json([
                    'success' => false,
                    'error' => 'Image editing is only supported with gpt-image-1. Current model: ' . $imageModel,
                ], 422);
            }

            $editPayload = [
                ['name' => 'model', 'contents' => $imageModel],
                ['name' => 'prompt', 'contents' => $prompt],
                ['name' => 'size', 'contents' => $imageSize],
                ['name' => 'n', 'contents' => '1'],
                ['name' => 'image[]', 'contents' => fopen($uploadedFile->getRealPath(), 'r'), 'filename' => $uploadedFile->getClientOriginalName()],
            ];

            if (app()->environment('local')) {
                $debugPrompt = [
                    'api' => 'OpenAI',
                    'endpoint' => 'https://api.openai.com/v1/images/edits',
                    'payload' => ['model' => $imageModel, 'prompt' => $prompt, 'size' => $imageSize, 'image' => $uploadedFile->getClientOriginalName()],
                ];
            }

            $response = Http::timeout(120)->withHeaders([
                'Authorization' => 'Bearer ' . config('services.openai.api_key'),
            ])->asMultipart()->post('https://api.openai.com/v1/images/edits', $editPayload);

            if ($response->successful()) {
                $data = $response->json();
                $b64Json = $data['data'][0]['b64_json'] ?? null;
                $imageUrl = $data['data'][0]['url'] ?? null;

                if ($b64Json) {
                    $image = imagecreatefromstring(base64_decode($b64Json));
                    $filename = 'generated-images/' . Str::uuid() . '.jpg';
                    $storagePath = Storage::disk('public')->path($filename);
                    Storage::disk('public')->makeDirectory('generated-images');
                    imagejpeg($image, $storagePath, 80);
                    imagedestroy($image);
                    $imageUrl = asset('storage/' . $filename);
                }

                if ($imageUrl) {
                    $this->logRun($node, $logInputs, '[Edited Image] ' . $imageUrl);

                    $meta = $this->getLocalImageMeta($imageUrl);
                    $responseData = [
                        'success' => true,
                        'image_url' => $imageUrl,
                        'image_width' => $meta['width'],
                        'image_height' => $meta['height'],
                        'image_filesize' => $meta['filesize'],
                    ];
                    if ($debugPrompt) {
                        $responseData['debug_prompt'] = $debugPrompt;
                    }
                    return response()->json($responseData);
                }

                return response()->json([
                    'success' => false,
                    'error' => 'No image data in edit response',
                ], 422);
            }

            $error = "OpenAI Edit API Error: " . $response->status() . " - " . $response->body();
            return response()->json(['success' => false, 'error' => $error], 422);
        }

        if ($this->isGeminiModel($imageModel)) {
            // Gemini Imagen API
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$imageModel}:predict";
            $apiPayload = [
                'instances' => [['prompt' => $prompt]],
                'parameters' => [
                    'sampleCount' => 1,
                    'aspectRatio' => $imageSize,
                ],
            ];

            if (app()->environment('local')) {
                $debugPrompt = [
                    'api' => 'Gemini',
                    'endpoint' => $endpoint,
                    'payload' => $apiPayload,
                ];
            }

            $response = Http::timeout(120)->withHeaders([
                'x-goog-api-key' => config('services.gemini.api_key'),
                'Content-Type' => 'application/json',
            ])->post($endpoint, $apiPayload);

            if ($response->successful()) {
                $data = $response->json();
                $b64 = $data['predictions'][0]['bytesBase64Encoded'] ?? null;

                if ($b64) {
                    $image = imagecreatefromstring(base64_decode($b64));
                    $filename = 'generated-images/' . Str::uuid() . '.jpg';
                    $storagePath = Storage::disk('public')->path($filename);
                    Storage::disk('public')->makeDirectory('generated-images');
                    imagejpeg($image, $storagePath, 80);
                    imagedestroy($image);
                    $imageUrl = asset('storage/' . $filename);
                    $imageInfo = getimagesize($storagePath);

                    $this->logRun($node, $logInputs, '[Generated Image] ' . $imageUrl);

                    $responseData = [
                        'success' => true,
                        'image_url' => $imageUrl,
                        'image_width' => $imageInfo[0] ?? null,
                        'image_height' => $imageInfo[1] ?? null,
                        'image_filesize' => filesize($storagePath),
                    ];
                    if ($debugPrompt) {
                        $responseData['debug_prompt'] = $debugPrompt;
                    }
                    return response()->json($responseData);
                }

                return response()->json([
                    'success' => false,
                    'error' => 'No image data in Gemini response',
                ], 422);
            }

            $error = "Gemini API Error: " . $response->status() . " - " . $response->body();

            return response()->json([
                'success' => false,
                'error' => $error,
            ], 422);
        }

        // OpenAI Image API
        $apiPayload = [
            'model' => $imageModel,
            'prompt' => $prompt,
            'n' => 1,
            'size' => $imageSize,
        ];

        if (app()->environment('local')) {
            $debugPrompt = [
                'api' => 'OpenAI',
                'endpoint' => 'https://api.openai.com/v1/images/generations',
                'payload' => $apiPayload,
            ];
        }

        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.api_key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/images/generations', $apiPayload);

        if ($response->successful()) {
            $data = $response->json();

            // gpt-image-1 returns base64 data, DALL-E 3 returns URLs
            $imageUrl = $data['data'][0]['url'] ?? null;
            $b64Json = $data['data'][0]['b64_json'] ?? null;

            if ($b64Json) {
                $image = imagecreatefromstring(base64_decode($b64Json));
                $filename = 'generated-images/' . Str::uuid() . '.jpg';
                $storagePath = Storage::disk('public')->path($filename);
                Storage::disk('public')->makeDirectory('generated-images');
                imagejpeg($image, $storagePath, 80);
                imagedestroy($image);
                $imageUrl = asset('storage/' . $filename);
            }

            if ($imageUrl) {
                $this->logRun($node, $logInputs, '[Generated Image] ' . $imageUrl);

                $meta = $this->getLocalImageMeta($imageUrl);
                $responseData = [
                    'success' => true,
                    'image_url' => $imageUrl,
                    'image_width' => $meta['width'],
                    'image_height' => $meta['height'],
                    'image_filesize' => $meta['filesize'],
                ];
                if ($debugPrompt) {
                    $responseData['debug_prompt'] = $debugPrompt;
                }
                return response()->json($responseData);
            }

            return response()->json([
                'success' => false,
                'error' => 'No image data in response',
            ], 422);
        }

        $error = "OpenAI API Error: " . $response->status() . " - " . $response->body();

        return response()->json([
            'success' => false,
            'error' => $error,
        ], 422);
    }

    public function downloadImage(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'filename' => 'nullable|string|max:255',
        ]);

        $imageUrl = $validated['url'];
        $filename = $validated['filename'] ?? 'image-' . date('Y-m-d') . '.jpg';

        try {
            // Check if this is a locally stored image
            $storageUrl = asset('storage/generated-images/');
            if (str_starts_with($imageUrl, $storageUrl)) {
                $relativePath = 'generated-images/' . basename($imageUrl);
                if (Storage::disk('public')->exists($relativePath)) {
                    $filePath = Storage::disk('public')->path($relativePath);
                    $contentType = str_ends_with($filePath, '.jpg') ? 'image/jpeg' : 'image/png';
                    return response()->file($filePath, [
                        'Content-Type' => $contentType,
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]);
                }
            }

            $response = Http::timeout(30)->get($imageUrl);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to fetch image',
                ], 422);
            }

            $contentType = $response->header('Content-Type') ?? 'image/png';

            return response($response->body())
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error downloading image: ' . $e->getMessage(),
            ], 422);
        }
    }

    protected function getLocalImageMeta(string $imageUrl): array
    {
        $storageUrl = asset('storage/generated-images/');
        if (str_starts_with($imageUrl, $storageUrl)) {
            $relativePath = 'generated-images/' . basename($imageUrl);
            $filePath = Storage::disk('public')->path($relativePath);
            if (file_exists($filePath)) {
                $info = getimagesize($filePath);
                return [
                    'width' => $info[0] ?? null,
                    'height' => $info[1] ?? null,
                    'filesize' => filesize($filePath),
                ];
            }
        }
        return ['width' => null, 'height' => null, 'filesize' => null];
    }

    protected function logRun(Node $node, array $inputs, string $output): void
    {
        $user = Auth::user();

        ActivityLog::create([
            'loggable_type' => Node::class,
            'loggable_id' => $node->id,
            'loggable_name' => $node->name,
            'action' => 'run',
            'changes' => [
                'inputs' => $inputs,
                'output' => $output,
            ],
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'created_at' => now(),
        ]);
    }

    public function destroy(Node $node)
    {
        $node->delete();

        return redirect()->route('nodes.index')->with('status', 'Node deleted successfully.');
    }

    public function copy(Node $node)
    {
        $copy = $node->duplicate();

        return redirect()->route('nodes.edit', $copy)->with('status', 'Node copied successfully.');
    }
}
