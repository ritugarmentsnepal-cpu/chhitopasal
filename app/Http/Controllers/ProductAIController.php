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
        
        $prompt = "You are an expert e-commerce copywriter. I need a compelling product title and a detailed, persuasive product description for my online store.\n";
        $prompt .= "The rough product name is: \"{$productName}\".\n";
        $prompt .= "Please write a catchy, SEO-friendly Title (max 60 characters) and a detailed Description (100-300 words). Focus on benefits, features, and appealing language.\n";
        $prompt .= "Respond ONLY with a valid JSON object matching this exact structure: {\"title\": \"Your Title\", \"description\": \"Your description here\"}. Do not include markdown code blocks around the JSON.";

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

            if (json_last_error() !== JSON_ERROR_NONE || !isset($parsedData['title']) || !isset($parsedData['description'])) {
                Log::error('OpenRouter JSON Parse Error: ' . $messageContent);
                return response()->json(['error' => 'AI returned malformed data. Please try again.'], 500);
            }

            return response()->json([
                'title' => $parsedData['title'],
                'description' => $parsedData['description']
            ]);

        } catch (\Exception $e) {
            Log::error('Product AI Generation Exception: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred while generating AI details.'], 500);
        }
    }
}
