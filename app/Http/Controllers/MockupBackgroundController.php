<?php

namespace App\Http\Controllers;

use App\Models\MockupBackground;
use App\Services\MockupAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MockupBackgroundController extends Controller
{
    /**
     * AI-generate a reusable background scene. Saved to the library
     * immediately so it can be reused for future template generations.
     */
    public function generate(Request $request, MockupAiService $ai)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'theme' => 'nullable|string|in:' . implode(',', array_keys(MockupAiService::THEME_PRESETS)),
            'lighting' => 'nullable|string|in:' . implode(',', array_keys(MockupAiService::LIGHTING_PRESETS)),
            'color_scheme' => 'nullable|string|max:500',
            'size' => 'nullable|string|in:' . implode(',', array_keys(MockupAiService::SIZE_PRESETS)),
        ]);

        try {
            $path = $ai->generateBackgroundImage($request->only(['theme', 'lighting', 'color_scheme', 'size']));
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $theme = $request->input('theme', 'studio');
        $background = MockupBackground::create([
            'name' => $request->input('name') ?: (ucwords(str_replace('_', ' ', $theme)) . ' — ' . now()->format('j M')),
            'image_path' => $path,
            'theme' => $theme,
            'lighting' => $request->input('lighting', 'soft'),
            'color_scheme' => $request->input('color_scheme'),
            'size' => $request->input('size', 'square'),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'background' => [
                'id' => $background->id,
                'name' => $background->name,
                'url' => '/storage/' . $background->image_path,
            ],
        ]);
    }

    public function destroy(MockupBackground $background)
    {
        // templates keep working — their background_id FK nulls on delete
        if (Storage::disk('public')->exists($background->image_path)) {
            Storage::disk('public')->delete($background->image_path);
        }

        $background->delete();

        return response()->json(['success' => true]);
    }
}
