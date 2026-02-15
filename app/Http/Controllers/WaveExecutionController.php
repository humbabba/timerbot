<?php

namespace App\Http\Controllers;

use App\Helpers\TextFilter;
use App\Models\ActivityLog;
use App\Models\Wave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WaveExecutionController extends Controller
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
        // Case-insensitive key lookup (input field might be "Model", "model", etc.)
        $rawValue = '';
        foreach ($inputs as $key => $val) {
            if (strtolower($key) === 'model') {
                $rawValue = $val;
                break;
            }
        }
        $value = strtolower(trim($rawValue));

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
     * Generate image using OpenAI or Gemini Imagen API.
     */
    protected function generateImage(string $prompt, string $size = '1536x1024', string $model = null): array
    {
        $model = $model ?? config('services.openai.model');

        if ($this->isGeminiModel($model)) {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:predict";

            $response = Http::timeout(120)->withHeaders([
                'x-goog-api-key' => config('services.gemini.api_key'),
                'Content-Type' => 'application/json',
            ])->post($endpoint, [
                'instances' => [['prompt' => $prompt]],
                'parameters' => [
                    'sampleCount' => 1,
                    'aspectRatio' => $size,
                ],
            ]);

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

                    return [
                        'success' => true,
                        'image_url' => $imageUrl,
                        'image_width' => $imageInfo[0] ?? null,
                        'image_height' => $imageInfo[1] ?? null,
                        'image_filesize' => filesize($storagePath),
                    ];
                }

                return [
                    'success' => false,
                    'error' => 'No image data in Gemini response',
                ];
            }

            return [
                'success' => false,
                'error' => "Gemini API Error: " . $response->status() . " - " . $response->body(),
            ];
        }

        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.api_key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/images/generations', [
            'model' => $model,
            'prompt' => $prompt,
            'n' => 1,
            'size' => $size,
        ]);

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
                $meta = $this->getLocalImageMeta($imageUrl);
                return [
                    'success' => true,
                    'image_url' => $imageUrl,
                    'image_width' => $meta['width'],
                    'image_height' => $meta['height'],
                    'image_filesize' => $meta['filesize'],
                ];
            }

            return [
                'success' => false,
                'error' => 'No image data in response',
            ];
        }

        return [
            'success' => false,
            'error' => "OpenAI API Error: " . $response->status() . " - " . $response->body(),
        ];
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

    public function start(Wave $wave)
    {
        $wave->load('nodes.voiceProfile.rewrites');

        if ($wave->nodes->isEmpty()) {
            return redirect()->route('waves.show', $wave)
                ->with('error', 'This wave has no nodes configured.');
        }

        // Prepare node data for JavaScript
        $waveNodesJson = $wave->nodes->map(function ($node) {
            return [
                'id' => $node->id,
                'name' => $node->name,
                'inputs' => $node->inputs ?? [],
                'position' => $node->pivot->position,
                'mappings' => json_decode($node->pivot->mappings ?? '{}', true) ?: [],
                'url_source_field' => $node->url_source_field,
                'url_target_field' => $node->url_target_field,
                'include_in_output' => $node->pivot->include_in_output ?? true,
                'voice_profile_name' => $node->voiceProfile?->name,
                'system_text' => $node->system_text,
                'style_check' => (bool) $node->style_check,
            ];
        })->values();

        $isFavorite = Auth::user()->favoriteWaves()->where('waves.id', $wave->id)->exists();

        return view('waves.execute', compact('wave', 'waveNodesJson', 'isFavorite'));
    }

    public function runStep(Request $request, Wave $wave)
    {
        $wave->load('nodes.voiceProfile.rewrites');

        $currentPosition = (int) $request->input('position', 0);
        $executionStateJson = $request->input('execution_state', '{}');
        $executionState = is_string($executionStateJson) ? json_decode($executionStateJson, true) ?? [] : $executionStateJson;
        $userInputs = $request->input('inputs', []);

        // Get node at current position
        $node = $wave->nodes->first(function ($n) use ($currentPosition) {
            return $n->pivot->position == $currentPosition;
        });

        if (!$node) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid step position: ' . $currentPosition,
            ], 422);
        }

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
                default => $rule[] = 'string',
            };

            $rules['inputs.' . $input['name']] = $rule;
        }

        $validated = $request->validate($rules);
        $validatedInputs = $validated['inputs'] ?? [];

        // Build user message content from inputs
        $contentParts = [];
        foreach ($node->inputs ?? [] as $input) {
            $value = $validatedInputs[$input['name']] ?? '';
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $contentParts[] = "{$input['label']}: {$value}";
        }
        $content = implode("\n", $contentParts);

        // Add context from previous steps to avoid repetitive language
        $previousOutputs = $this->getPreviousOutputsContext($executionState, $currentPosition);
        if (!empty($previousOutputs)) {
            $content .= "\n\n---\nPREVIOUS CONTENT (avoid repeating language, phrases, or information from these sections):\n" . $previousOutputs;
        }

        // Check if this node requires image generation
        $isImageNode = $this->requiresImageGeneration($node->system_text);

        $debugPrompt = null;

        if ($isImageNode) {
            // Build prompt from system_text and user inputs
            $prompt = $node->system_text ?? '';

            // Replace {field} placeholders with actual values
            foreach ($node->inputs ?? [] as $input) {
                $value = $validatedInputs[$input['name']] ?? '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $prompt = str_ireplace('{' . $input['name'] . '}', $value, $prompt);
            }

            // If no placeholders were replaced, append user inputs
            if ($prompt === ($node->system_text ?? '')) {
                $inputValues = [];
                foreach ($node->inputs ?? [] as $input) {
                    $value = $validatedInputs[$input['name']] ?? '';
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    if (!empty($value)) {
                        $inputValues[] = $value;
                    }
                }
                if (!empty($inputValues)) {
                    $prompt .= ' ' . implode(', ', $inputValues);
                }
            }

            // Resolve image model (allow override via input named "model")
            $imageModel = $this->resolveImageModel($validatedInputs);
            $imageSize = $this->getImageSize($prompt, $imageModel);

            if (app()->environment('local')) {
                if ($this->isGeminiModel($imageModel)) {
                    $debugPrompt = [
                        'api' => 'Gemini',
                        'endpoint' => "https://generativelanguage.googleapis.com/v1beta/models/{$imageModel}:predict",
                        'payload' => [
                            'instances' => [['prompt' => $prompt]],
                            'parameters' => [
                                'sampleCount' => 1,
                                'aspectRatio' => $imageSize,
                            ],
                        ],
                    ];
                } else {
                    $debugPrompt = [
                        'api' => 'OpenAI',
                        'endpoint' => 'https://api.openai.com/v1/images/generations',
                        'payload' => [
                            'model' => $imageModel,
                            'prompt' => $prompt,
                            'n' => 1,
                            'size' => $imageSize,
                        ],
                    ];
                }
            }

            $imageResult = $this->generateImage($prompt, $imageSize, $imageModel);

            if (!$imageResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $imageResult['error'],
                    'debug_prompt' => $debugPrompt,
                ], 422);
            }

            $resultText = '[Generated Image]';
            $htmlResult = '<img src="' . htmlspecialchars($imageResult['image_url']) . '" alt="Generated image" style="max-width: 100%; height: auto;">';
            $isImage = true;
            $imageUrl = $imageResult['image_url'];
            $imageWidth = $imageResult['image_width'] ?? null;
            $imageHeight = $imageResult['image_height'] ?? null;
            $imageFilesize = $imageResult['image_filesize'] ?? null;
        } else {
            // Build system prompt, prepending voice profile if assigned
            $systemBlocks = [];
            if ($node->voice_profile_id && $node->voiceProfile) {
                $systemBlocks[] = [
                    'type' => 'text',
                    'text' => $node->voiceProfile->content,
                    'cache_control' => ['type' => 'ephemeral'],
                ];

                // Inject voice rewrite examples if any exist
                $rewrites = $node->voiceProfile->rewrites;
                if ($rewrites->isNotEmpty()) {
                    $systemBlocks[] = [
                        'type' => 'text',
                        'text' => $this->buildRewriteBlock($rewrites),
                        'cache_control' => ['type' => 'ephemeral'],
                    ];
                }
            }

            // Replace {field} placeholders in system_text with actual input values
            $systemText = $node->system_text ?? '';
            foreach ($node->inputs ?? [] as $input) {
                $value = $validatedInputs[$input['name']] ?? '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $systemText = str_ireplace('{' . $input['name'] . '}', $value, $systemText);
            }

            $systemBlocks[] = [
                'type' => 'text',
                'text' => $systemText,
                'cache_control' => ['type' => 'ephemeral'],
            ];

            // Execute Claude API call
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

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => "API Error: " . $response->status() . " - " . $response->body(),
                ], 422);
            }

            $data = $response->json();
            $resultText = $data['content'][0]['text'] ?? 'No response content';
            $resultText = TextFilter::filterProfanity($resultText);
            $htmlResult = Str::markdown($resultText, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            $isImage = false;
            $imageUrl = null;
            $imageWidth = null;
            $imageHeight = null;
            $imageFilesize = null;
        }

        // Store this step's execution in state
        $executionState[$currentPosition] = [
            'node_id' => $node->id,
            'node_name' => $node->name,
            'inputs' => $validatedInputs,
            'output' => $resultText,
            'output_html' => $htmlResult,
            'is_image' => $isImage,
            'image_url' => $imageUrl,
            'image_width' => $imageWidth,
            'image_height' => $imageHeight,
            'image_filesize' => $imageFilesize,
        ];

        $this->logWaveRun($wave, $node->name, $currentPosition, $validatedInputs, $resultText);

        $nextPosition = $currentPosition + 1;
        $nextNode = $wave->nodes->first(function ($n) use ($nextPosition) {
            return $n->pivot->position == $nextPosition;
        });

        if (!$nextNode) {
            // No more nodes - return final results
            $responseData = [
                'success' => true,
                'completed' => true,
                'execution_state' => $executionState,
                'results' => $this->formatFinalResults($executionState),
            ];
            if ($debugPrompt) {
                $responseData['debug_prompt'] = $debugPrompt;
            }
            return response()->json($responseData);
        }

        $mappings = json_decode($nextNode->pivot->mappings ?? '{}', true) ?: [];

        // Apply mappings to get pre-filled values
        $preFilled = $this->applyMappings($mappings, $executionState);

        // Check if all required inputs are mapped
        $allRequiredMapped = $this->checkAllRequiredMapped($nextNode, $preFilled);

        $responseData = [
            'success' => true,
            'completed' => false,
            'execution_state' => $executionState,
            'next_step' => [
                'position' => $nextPosition,
                'node' => [
                    'id' => $nextNode->id,
                    'name' => $nextNode->name,
                    'inputs' => $nextNode->inputs,
                ],
                'pre_filled' => $preFilled,
                'auto_run' => $allRequiredMapped,
            ],
        ];
        if ($debugPrompt) {
            $responseData['debug_prompt'] = $debugPrompt;
        }
        return response()->json($responseData);
    }

    protected function applyMappings(array $mappings, array $executionState): array
    {
        $preFilled = [];

        foreach ($mappings as $targetField => $mapping) {
            $type = $mapping['type'] ?? '';

            // Skip if no mapping type selected
            if (empty($type)) {
                continue;
            }

            $sourcePosition = (int) ($mapping['source_position'] ?? 0);
            $sourceField = $mapping['source_field'] ?? null;

            if (!isset($executionState[$sourcePosition])) {
                continue;
            }

            $sourceStep = $executionState[$sourcePosition];

            if ($type === 'output') {
                $preFilled[$targetField] = $sourceStep['output'] ?? '';
            } elseif ($type === 'input' && !empty($sourceField)) {
                $preFilled[$targetField] = $sourceStep['inputs'][$sourceField] ?? '';
            }
        }

        return $preFilled;
    }

    protected function checkAllRequiredMapped($node, array $preFilled): bool
    {
        foreach ($node->inputs ?? [] as $input) {
            if (!empty($input['required'])) {
                $fieldName = $input['name'];
                if (!isset($preFilled[$fieldName]) || trim($preFilled[$fieldName]) === '') {
                    return false;
                }
            }
        }

        return true;
    }

    protected function formatFinalResults(array $executionState): array
    {
        $results = [];

        foreach ($executionState as $position => $step) {
            $results[] = [
                'position' => $position,
                'node_name' => $step['node_name'],
                'node_id' => $step['node_id'],
                'inputs' => $step['inputs'],
                'output_html' => $step['output_html'],
                'is_image' => $step['is_image'] ?? false,
                'image_url' => $step['image_url'] ?? null,
                'image_width' => $step['image_width'] ?? null,
                'image_height' => $step['image_height'] ?? null,
                'image_filesize' => $step['image_filesize'] ?? null,
            ];
        }

        return $results;
    }

    protected function getPreviousOutputsContext(array $executionState, int $currentPosition): string
    {
        $previousOutputs = [];

        for ($i = 0; $i < $currentPosition; $i++) {
            if (isset($executionState[$i]) && !empty($executionState[$i]['output'])) {
                $nodeName = $executionState[$i]['node_name'] ?? "Step " . ($i + 1);
                $previousOutputs[] = "[{$nodeName}]:\n" . $executionState[$i]['output'];
            }
        }

        return implode("\n\n", $previousOutputs);
    }

    public function rerunStep(Request $request, Wave $wave)
    {
        $wave->load('nodes.voiceProfile.rewrites');

        $position = (int) $request->input('position', 0);
        $userInputs = $request->input('inputs', []);
        $executionStateJson = $request->input('execution_state', '{}');
        $executionState = is_string($executionStateJson) ? json_decode($executionStateJson, true) ?? [] : $executionStateJson;

        // Get node at position
        $node = $wave->nodes->first(function ($n) use ($position) {
            return $n->pivot->position == $position;
        });

        if (!$node) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid step position: ' . $position,
            ], 422);
        }

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
                default => $rule[] = 'string',
            };

            $rules['inputs.' . $input['name']] = $rule;
        }

        $validated = $request->validate($rules);
        $validatedInputs = $validated['inputs'] ?? [];

        // Build user message content from inputs
        $contentParts = [];
        foreach ($node->inputs ?? [] as $input) {
            $value = $validatedInputs[$input['name']] ?? '';
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $contentParts[] = "{$input['label']}: {$value}";
        }
        $content = implode("\n", $contentParts);

        // Add context from previous steps to avoid repetitive language
        $previousOutputs = $this->getPreviousOutputsContext($executionState, $position);
        if (!empty($previousOutputs)) {
            $content .= "\n\n---\nPREVIOUS CONTENT (avoid repeating language, phrases, or information from these sections):\n" . $previousOutputs;
        }

        // Check if this node requires image generation
        $isImageNode = $this->requiresImageGeneration($node->system_text);

        $debugPrompt = null;

        if ($isImageNode) {
            // Build prompt from system_text and user inputs
            $prompt = $node->system_text ?? '';

            // Replace {field} placeholders with actual values
            foreach ($node->inputs ?? [] as $input) {
                $value = $validatedInputs[$input['name']] ?? '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $prompt = str_ireplace('{' . $input['name'] . '}', $value, $prompt);
            }

            // If no placeholders were replaced, append user inputs
            if ($prompt === ($node->system_text ?? '')) {
                $inputValues = [];
                foreach ($node->inputs ?? [] as $input) {
                    $value = $validatedInputs[$input['name']] ?? '';
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    if (!empty($value)) {
                        $inputValues[] = $value;
                    }
                }
                if (!empty($inputValues)) {
                    $prompt .= ' ' . implode(', ', $inputValues);
                }
            }

            // Resolve image model (allow override via input named "model")
            $imageModel = $this->resolveImageModel($validatedInputs);
            $imageSize = $this->getImageSize($prompt, $imageModel);

            if (app()->environment('local')) {
                if ($this->isGeminiModel($imageModel)) {
                    $debugPrompt = [
                        'api' => 'Gemini',
                        'endpoint' => "https://generativelanguage.googleapis.com/v1beta/models/{$imageModel}:predict",
                        'payload' => [
                            'instances' => [['prompt' => $prompt]],
                            'parameters' => [
                                'sampleCount' => 1,
                                'aspectRatio' => $imageSize,
                            ],
                        ],
                    ];
                } else {
                    $debugPrompt = [
                        'api' => 'OpenAI',
                        'endpoint' => 'https://api.openai.com/v1/images/generations',
                        'payload' => [
                            'model' => $imageModel,
                            'prompt' => $prompt,
                            'n' => 1,
                            'size' => $imageSize,
                        ],
                    ];
                }
            }

            $imageResult = $this->generateImage($prompt, $imageSize, $imageModel);

            if (!$imageResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $imageResult['error'],
                    'debug_prompt' => $debugPrompt,
                ], 422);
            }

            $resultText = '[Generated Image]';
            $htmlResult = '<img src="' . htmlspecialchars($imageResult['image_url']) . '" alt="Generated image" style="max-width: 100%; height: auto;">';
            $isImage = true;
            $imageUrl = $imageResult['image_url'];
            $imageWidth = $imageResult['image_width'] ?? null;
            $imageHeight = $imageResult['image_height'] ?? null;
            $imageFilesize = $imageResult['image_filesize'] ?? null;
        } else {
            // Build system prompt, prepending voice profile if assigned
            $systemBlocks = [];
            if ($node->voice_profile_id && $node->voiceProfile) {
                $systemBlocks[] = [
                    'type' => 'text',
                    'text' => $node->voiceProfile->content,
                    'cache_control' => ['type' => 'ephemeral'],
                ];

                // Inject voice rewrite examples if any exist
                $rewrites = $node->voiceProfile->rewrites;
                if ($rewrites->isNotEmpty()) {
                    $systemBlocks[] = [
                        'type' => 'text',
                        'text' => $this->buildRewriteBlock($rewrites),
                        'cache_control' => ['type' => 'ephemeral'],
                    ];
                }
            }

            // Replace {field} placeholders in system_text with actual input values
            $systemText = $node->system_text ?? '';
            foreach ($node->inputs ?? [] as $input) {
                $value = $validatedInputs[$input['name']] ?? '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $systemText = str_ireplace('{' . $input['name'] . '}', $value, $systemText);
            }

            $systemBlocks[] = [
                'type' => 'text',
                'text' => $systemText,
                'cache_control' => ['type' => 'ephemeral'],
            ];

            // Execute Claude API call
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

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => "API Error: " . $response->status() . " - " . $response->body(),
                ], 422);
            }

            $data = $response->json();
            $resultText = $data['content'][0]['text'] ?? 'No response content';
            $resultText = TextFilter::filterProfanity($resultText);
            $htmlResult = Str::markdown($resultText, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            $isImage = false;
            $imageUrl = null;
            $imageWidth = null;
            $imageHeight = null;
            $imageFilesize = null;
        }

        $this->logWaveRun($wave, $node->name, $position, $validatedInputs, $resultText);

        $responseData = [
            'success' => true,
            'result' => [
                'position' => $position,
                'node_name' => $node->name,
                'node_id' => $node->id,
                'inputs' => $validatedInputs,
                'output' => $resultText,
                'output_html' => $htmlResult,
                'is_image' => $isImage,
                'image_url' => $imageUrl,
                'image_width' => $imageWidth,
                'image_height' => $imageHeight,
                'image_filesize' => $imageFilesize,
            ],
        ];
        if ($debugPrompt) {
            $responseData['debug_prompt'] = $debugPrompt;
        }
        return response()->json($responseData);
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

    protected function logWaveRun(Wave $wave, string $nodeName, int $position, array $inputs, string $output): void
    {
        $user = Auth::user();

        ActivityLog::create([
            'loggable_type' => Wave::class,
            'loggable_id' => $wave->id,
            'loggable_name' => $wave->name,
            'action' => 'run',
            'changes' => [
                'step' => $position + 1,
                'node' => $nodeName,
                'inputs' => $inputs,
                'output' => $output,
            ],
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'created_at' => now(),
        ]);
    }
}
