<?php

namespace App\Http\Controllers;

use App\Models\MockupTemplate;
use App\Services\MockupAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MockupTemplateController extends Controller
{
    /**
     * Manually upload a ready-made template image (fallback / non-AI path).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'image' => 'required|image|max:10240',
        ]);

        $path = $request->file('image')->store('mockup_templates', 'public');

        MockupTemplate::create([
            'name' => $request->name,
            'product_type' => $request->product_type,
            'image_path' => $path,
            'is_ai_generated' => false,
        ]);

        return back()->with('success', 'Mockup template added successfully.');
    }

    /**
     * AI-generate a template image (preview step — no DB record yet).
     * Returns the stored image path so the user can review, regenerate,
     * or confirm via saveGenerated().
     */
    public function generate(Request $request, MockupAiService $ai)
    {
        $request->validate([
            'product_type' => 'required|string|max:100',
            'custom_product' => 'nullable|string|max:255',
            'size' => 'nullable|string|in:' . implode(',', array_keys(MockupAiService::SIZE_PRESETS)),
            'theme' => 'nullable|string|in:' . implode(',', array_keys(MockupAiService::THEME_PRESETS)),
            'presentation' => 'nullable|string|in:' . implode(',', array_keys(MockupAiService::PRESENTATION_PRESETS)),
            'angle' => 'nullable|string|in:' . implode(',', array_keys(MockupAiService::ANGLE_PRESETS)),
            'lighting' => 'nullable|string|in:' . implode(',', array_keys(MockupAiService::LIGHTING_PRESETS)),
            'views' => 'nullable|string|in:' . implode(',', array_keys(MockupAiService::VIEW_PRESETS)),
            'color_scheme' => 'nullable|string|max:500',
            'placements' => 'nullable|string|max:500',
            'style_notes' => 'nullable|string|max:1000',
            'logo_coverage' => 'nullable|string|in:' . implode(',', array_keys(MockupAiService::PLACEHOLDER_COVERAGE)),
            'reference_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            // When regenerating, reuse the previously uploaded reference
            'reference_path' => 'nullable|string|max:255',
        ]);

        // Persist the product reference photo (reused across regenerations)
        $referencePath = null;
        if ($request->hasFile('reference_image')) {
            $referencePath = $request->file('reference_image')->store('mockup_templates/sources', 'public');
        } elseif ($request->filled('reference_path')) {
            $candidate = $request->input('reference_path');
            if (Str::startsWith($candidate, 'mockup_templates/sources/') && Storage::disk('public')->exists($candidate)) {
                $referencePath = $candidate;
            }
        }

        try {
            $path = $ai->generateTemplateImage($request->only([
                'product_type', 'custom_product', 'size', 'theme',
                'presentation', 'angle', 'lighting', 'views',
                'color_scheme', 'placements', 'style_notes', 'logo_coverage',
            ]), $referencePath);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => '/storage/' . $path,
            'reference_path' => $referencePath,
        ]);
    }

    /**
     * Confirm a previously generated template image into the library.
     */
    public function saveGenerated(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:255',
            'product_type' => 'required|string|max:100',
            'custom_product' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:50',
            'theme' => 'nullable|string|max:50',
            'presentation' => 'nullable|string|max:50',
            'angle' => 'nullable|string|max:50',
            'lighting' => 'nullable|string|max:50',
            'views' => 'nullable|string|max:50',
            'color_scheme' => 'nullable|string|max:500',
            'placements' => 'nullable|string|max:500',
            'style_notes' => 'nullable|string|max:1000',
            'reference_path' => 'nullable|string|max:255',
        ]);

        $path = $request->input('path');

        // Only accept files this feature generated itself
        if (!Str::startsWith($path, 'mockup_templates/ai_') || !Storage::disk('public')->exists($path)) {
            return response()->json(['success' => false, 'message' => 'Generated image not found.'], 422);
        }

        $referencePath = $request->input('reference_path');
        if ($referencePath && !Str::startsWith($referencePath, 'mockup_templates/sources/')) {
            $referencePath = null;
        }

        $productType = $request->input('product_type') === 'other' && $request->filled('custom_product')
            ? Str::slug($request->input('custom_product'), '_')
            : $request->input('product_type');

        $template = MockupTemplate::create([
            'name' => $request->input('name'),
            'product_type' => $productType,
            'size' => $request->input('size'),
            'theme' => $request->input('theme'),
            'color_scheme' => $request->input('color_scheme'),
            'placements' => $request->input('placements'),
            'style_notes' => $request->input('style_notes'),
            'options' => array_filter($request->only(['presentation', 'angle', 'lighting', 'views'])),
            'image_path' => $path,
            'source_image_path' => $referencePath,
            'is_ai_generated' => true,
        ]);

        // PHASE-2.4: mark the generation attempt as confirmed
        \App\Models\AiGeneration::where('image_path', $path)->update(['template_id' => $template->id]);

        return response()->json(['success' => true, 'template' => $template]);
    }

    public function destroy(MockupTemplate $template)
    {
        if (Storage::disk('public')->exists($template->image_path)) {
            Storage::disk('public')->delete($template->image_path);
        }

        // Remove the product reference photo if no other template shares it
        if ($template->source_image_path) {
            $shared = MockupTemplate::where('source_image_path', $template->source_image_path)
                ->where('id', '!=', $template->id)
                ->exists();
            if (!$shared && Storage::disk('public')->exists($template->source_image_path)) {
                Storage::disk('public')->delete($template->source_image_path);
            }
        }

        $template->delete();

        return back()->with('success', 'Mockup template deleted successfully.');
    }
}
