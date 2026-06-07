<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductAIController extends Controller
{
    public function generate(Request $request)
    {
        if (!auth()->user()->hasPermission('products')) {
            return response()->json(['error' => 'Access Denied'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $apiKey = setting('openrouter_api_key', env('OPENROUTER_API_KEY'));
        if (empty($apiKey)) {
            return response()->json(['error' => 'OpenRouter API Key is not configured. Please add it in Settings > Integrations.'], 500);
        }

        $productName = $request->input('name');
        $price = $request->input('price');
        $color = $request->input('color_options');
        $size = $request->input('size_options');
        $weight = $request->input('weight_grams');
        
        $prompt = "You are an expert e-commerce copywriter. I need a compelling, persuasive product description for my online store in Nepal.\n";
        $prompt .= "Product Details:\n";
        $prompt .= "- Title: \"{$productName}\"\n";
        if (!empty($price)) $prompt .= "- Price: Rs. {$price}\n";
        if (!empty($color)) $prompt .= "- Colors available: {$color}\n";
        if (!empty($size)) $prompt .= "- Sizes available: {$size}\n";
        if (!empty($weight)) $prompt .= "- Weight: {$weight}g\n";
        
        $prompt .= "\nWrite a detailed description structured as a list of pointed highlights in brief.\n";
        $prompt .= "CRITICAL INSTRUCTION: You MUST write extremely short bullet points. Each bullet point must be ONLY 3 to 7 words maximum. DO NOT write full sentences. DO NOT write paragraphs.\n";
        $prompt .= "Use all the information provided above (title, price, colour, size, weight, fabric if evident from the image or title, etc.) to enrich the description.\n";
        $prompt .= "Naturally incorporate a few common Nepali words or phrases (like 'Ramro', 'Sasto', 'Majjako', 'Dammi', etc.) to appeal to the local Nepali audience.\n";
        $prompt .= "Respond ONLY with a valid JSON object where the 'description' key contains a SINGLE STRING of text. Use newlines (\\n) and bullet points inside the string. Exact structure expected: {\"description\": \"• Point 1\\n• Point 2\"}. Do not include markdown code blocks around the JSON.";

        $content = [
            [
                'type' => 'text',
                'text' => $prompt
            ]
        ];

        // If an image is provided, base64 encode it and append to content
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $mimeType = $imageFile->getMimeType();
            $base64Image = base64_encode(file_get_contents($imageFile->getRealPath()));
            
            $content[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => "data:{$mimeType};base64,{$base64Image}"
                ]
            ];
            
            $content[0]['text'] .= "\nI have also attached an image of the product. Please analyze it to extract key visual details, colors, and styling to enrich the description.";
        }

        try {
            // We use anthropic/claude-sonnet-4.6 as the default model, but allow override from settings
            $model = setting('openrouter_model', env('OPENROUTER_MODEL', 'anthropic/claude-sonnet-4.6'));
            
            // Forcefully catch and replace deprecated/invalid models
            if ($model === 'anthropic/claude-3.5-sonnet' || $model === 'anthropic/claude-sonnet-latest') {
                $model = 'anthropic/claude-sonnet-4.6';
            }
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'HTTP-Referer' => url('/'),
                'X-Title' => 'Chhito Pasal',
                'Content-Type' => 'application/json'
            ])
            ->timeout(60)
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $content
                    ]
                ],
            ]);

            if ($response->failed()) {
                Log::error('OpenRouter API Error: ' . $response->body());
                return response()->json(['error' => 'AI Provider Error: ' . $response->json('error.message', 'Unknown error')], 500);
            }

            $result = $response->json();
            $messageContent = $result['choices'][0]['message']['content'] ?? '';
            
            // Clean up the response in case the model returns markdown code blocks
            $messageContent = trim($messageContent);
            if (str_starts_with($messageContent, '```json')) {
                $messageContent = substr($messageContent, 7);
                if (str_ends_with($messageContent, '```')) {
                    $messageContent = substr($messageContent, 0, -3);
                }
            } elseif (str_starts_with($messageContent, '```')) {
                $messageContent = substr($messageContent, 3);
                if (str_ends_with($messageContent, '```')) {
                    $messageContent = substr($messageContent, 0, -3);
                }
            }
            $messageContent = trim($messageContent);

            $parsedData = json_decode($messageContent, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($parsedData['description'])) {
                Log::error('OpenRouter JSON Parse Error: ' . $messageContent);
                return response()->json(['error' => 'AI returned malformed data. Please try again.'], 500);
            }

            $descriptionStr = '';
            if (is_array($parsedData['description'])) {
                foreach ($parsedData['description'] as $item) {
                    if (is_string($item)) {
                        $descriptionStr .= "• " . $item . "\n";
                    } elseif (is_array($item)) {
                        $descriptionStr .= "• " . implode(" - ", array_values($item)) . "\n";
                    }
                }
            } else {
                $descriptionStr = (string)$parsedData['description'];
            }

            return response()->json([
                'description' => trim($descriptionStr)
            ]);

        } catch (\Exception $e) {
            Log::error('Product AI Generation Exception: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred while generating AI details.'], 500);
        }
    }

    public function generateThumbnails(Request $request)
    {
        if (!auth()->user()->hasPermission('products')) {
            return response()->json(['error' => 'Access Denied'], 403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $apiKey = setting('openrouter_api_key', env('OPENROUTER_API_KEY'));
        if (empty($apiKey)) {
            return response()->json(['error' => 'OpenRouter API Key is not configured.'], 500);
        }

        $imageFile = $request->file('image');
        $mimeType = $imageFile->getMimeType();
        
        // Optimize image size to speed up the API request upload
        $sourcePath = $imageFile->getRealPath();
        list($width, $height, $type) = getimagesize($sourcePath);
        $maxWidth = 800;
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = intval($height * ($maxWidth / $width));
            $thumb = imagecreatetruecolor($newWidth, $newHeight);
            
            if ($type == IMAGETYPE_JPEG) {
                $source = imagecreatefromjpeg($sourcePath);
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                ob_start();
                imagejpeg($thumb, null, 80);
                $imageData = ob_get_clean();
                imagedestroy($source);
            } elseif ($type == IMAGETYPE_PNG) {
                $source = imagecreatefrompng($sourcePath);
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                ob_start();
                imagepng($thumb, null, 8);
                $imageData = ob_get_clean();
                imagedestroy($source);
            } elseif ($type == IMAGETYPE_WEBP) {
                $source = imagecreatefromwebp($sourcePath);
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                ob_start();
                imagewebp($thumb, null, 80);
                $imageData = ob_get_clean();
                imagedestroy($source);
            } else {
                $imageData = file_get_contents($sourcePath);
            }
            imagedestroy($thumb);
            $base64Image = base64_encode($imageData);
        } else {
            $base64Image = base64_encode(file_get_contents($sourcePath));
        }

        $model = setting('openrouter_image_model', env('OPENROUTER_IMAGE_MODEL', 'openai/gpt-5-image')); // Valid image generation model

        try {
            // Reduced to 3 concurrent requests to prevent long timeouts and slow generation
            $responses = \Illuminate\Support\Facades\Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($apiKey, $model, $mimeType, $base64Image) {
                $reqs = [];
                for ($i = 1; $i <= 3; $i++) {
                    $reqs[] = $pool->withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'HTTP-Referer' => url('/'),
                        'X-Title' => 'Chhito Pasal',
                        'Content-Type' => 'application/json'
                    ])
                    ->timeout(60)
                    ->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => $model,
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'Generate a professional, high-quality e-commerce product thumbnail based on this image. Place the product on a clean studio background or highly aesthetic matching background. Ensure it looks exactly like a high-end product photo. Variation ' . $i
                                    ],
                                    [
                                        'type' => 'image_url',
                                        'image_url' => [
                                            'url' => "data:{$mimeType};base64,{$base64Image}"
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'modalities' => ['image']
                    ]);
                }
                return $reqs;
            });

            $urls = [];
            foreach ($responses as $response) {
                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $content = $response->json('choices.0.message.content', '');
                    // Extract URL or base64 from markdown if formatted as ![image](url)
                    if (preg_match('/!\[.*?\]\((.*?)\)/', $content, $matches)) {
                        $urls[] = $matches[1];
                    } else {
                        // Fallback to raw content assuming it might be a direct URL or base64 string
                        $urls[] = trim($content);
                    }
                } else {
                    Log::error('OpenRouter Image Generation Failed: ' . ($response instanceof \Illuminate\Http\Client\Response ? $response->body() : 'Pool error'));
                }
            }

            if (empty($urls)) {
                return response()->json(['error' => 'Failed to generate thumbnails. Ensure your OpenRouter API key is valid and the selected model supports image generation.'], 500);
            }

            return response()->json(['urls' => array_slice($urls, 0, 3)]);

        } catch (\Exception $e) {
            Log::error('Product AI Thumbnail Generation Exception: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred while generating AI thumbnails.'], 500);
        }
    }
}
