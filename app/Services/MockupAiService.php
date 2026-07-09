<?php

namespace App\Services;

use App\Models\MockupTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Generates mockup templates and customer mockups using an image-capable
 * AI model via OpenRouter (default: Gemini 2.5 Flash Image, ~$0.04/image).
 *
 * Two-step flow:
 *  1. Template generation — turn a product reference photo (or a text brief)
 *     into a clean, reusable mockup scene with a "YOUR LOGO" placeholder.
 *  2. Mockup generation — swap the placeholder branding on a template with
 *     the customer's real logo, leaving everything else untouched.
 */
class MockupAiService
{
    public const DEFAULT_MODEL = 'google/gemini-2.5-flash-image';

    /**
     * Aspect ratio presets selectable in the UI.
     */
    public const SIZE_PRESETS = [
        'square'    => 'a perfectly square 1:1',
        'portrait'  => 'a portrait 3:4',
        'landscape' => 'a landscape 4:3',
        'wide'      => 'a wide 16:9',
        'story'     => 'a tall 9:16',
    ];

    /**
     * How large the "YOUR LOGO" placeholder should be rendered on the
     * template. Bigger placeholders make downstream mockups size the real
     * logo up more reliably.
     */
    public const PLACEHOLDER_COVERAGE = [
        'small'  => 'small — a chest/pocket-mark sized placeholder, roughly 30% of the product\'s visible front width',
        'medium' => 'medium — a standard print, roughly 60% of the product\'s visible front width',
        'large'  => 'large — a big print spanning roughly 85% of the product\'s visible front width',
        'full'   => 'full-panel — the placeholder fills almost the ENTIRE printable front panel, edge to edge, as large as physically possible on the product surface',
    ];

    /**
     * Scene/background presets selectable in the UI.
     * (Legacy keys 'flat_lay' and 'hanging' moved to PRESENTATION_PRESETS but
     * remain here so old saved settings keep working.)
     */
    public const THEME_PRESETS = [
        'studio'    => 'a clean professional photo studio with a seamless neutral backdrop',
        'gradient'  => 'a smooth soft-colour gradient studio backdrop (subtle, modern e-commerce style)',
        'marble'    => 'an elegant white marble surface and softly blurred bright interior behind it',
        'wood'      => 'a warm natural wooden table surface with a softly blurred cosy background',
        'concrete'  => 'a modern textured concrete/stone surface with a minimalist urban feel',
        'linen'     => 'a soft draped linen fabric backdrop with gentle natural folds',
        'podium'    => 'a minimal display podium/pedestal hero scene, premium e-commerce style',
        'lifestyle' => 'a natural lifestyle interior with soft daylight and a tastefully blurred real-world background',
        'outdoor'   => 'a bright outdoor daylight scene with a softly blurred natural background',
        // legacy values from older templates
        'flat_lay'  => 'a clean styled flat-lay surface with minimal props',
        'hanging'   => 'a clean wall with natural shadows',
    ];

    /**
     * How the product is physically displayed in the scene.
     */
    public const PRESENTATION_PRESETS = [
        'product_only' => 'the product presented on its own, neatly styled and standing/laid naturally',
        'ghost'        => 'the product shown with an invisible ghost-mannequin effect so it holds a filled 3D shape with no person or mannequin visible',
        'model'        => 'the product worn/held by a real person whose face is cropped out of frame or turned away (no identifiable face anywhere in the image)',
        'hanging'      => 'the product displayed hanging on a wooden hanger or hook',
        'flat_lay'     => 'the product arranged top-down as a styled flat lay',
        'folded'       => 'the product neatly folded and stacked, boutique retail style',
    ];

    /**
     * Camera angle presets.
     */
    public const ANGLE_PRESETS = [
        'front'         => 'straight-on eye-level front view, perfectly centered',
        'three_quarter' => 'a three-quarter angle view that adds depth and dimension',
        'high'          => 'a slightly elevated ~30° downward angle',
        'low'           => 'a low hero angle looking slightly up at the product for a premium feel',
        'closeup'       => 'a closer crop that fills the frame with the product, keeping the whole print area fully visible',
    ];

    /**
     * Lighting presets.
     */
    public const LIGHTING_PRESETS = [
        'soft'     => 'soft, even, shadowless studio lighting',
        'warm'     => 'warm golden-toned lighting with gentle soft shadows',
        'dramatic' => 'directional lighting with deeper, dramatic shadows for a premium editorial look',
        'daylight' => 'bright natural window daylight with crisp soft shadows',
    ];

    /**
     * How many views of the product appear in the image.
     */
    public const VIEW_PRESETS = [
        'single'     => 'show the product once, as a single hero view',
        'front_back' => 'show the SAME product twice, side by side in the same scene — front view on the left and back view on the right',
        'grid'       => 'show the SAME product three times in one clean lineup — front view, back view and a three-quarter angle view',
    ];

    /**
     * Generate a mockup template image.
     *
     * @param array $options  product_type, custom_product, size, theme,
     *                        color_scheme, placements, style_notes
     * @param string|null $referenceImagePath  path on the public disk of the
     *                                         uploaded product reference photo
     * @return string  storage path (public disk) of the generated template
     */
    public function generateTemplateImage(array $options, ?string $referenceImagePath = null): string
    {
        $prompt = $this->buildTemplatePrompt($options, (bool) $referenceImagePath);

        $inputImages = $referenceImagePath ? [$referenceImagePath] : [];

        $binary = $this->generateImage($prompt, $inputImages);

        $path = 'mockup_templates/ai_' . uniqid() . '.png';
        Storage::disk('public')->put($path, $binary);

        $this->recordGeneration('template', $path);

        return $path;
    }

    /**
     * Generate a customer mockup by replacing the placeholder branding on a
     * template with the customer's logo.
     *
     * @param MockupTemplate $template
     * @param string $logoPath  path on the public disk of the customer logo
     * @param string|null $instructions  optional extra instructions
     * @return string  storage path (public disk) of the generated mockup
     */
    public function generateMockupImage(MockupTemplate $template, string $logoPath, ?string $instructions = null, string $logoSize = 'medium'): string
    {
        $prompt = $this->buildMockupPrompt($template, $instructions, $logoSize);

        $binary = $this->generateImage($prompt, [$template->image_path, $logoPath]);

        $path = 'mockups/ai_' . uniqid() . '.png';
        Storage::disk('public')->put($path, $binary);

        $this->recordGeneration('mockup', $path);

        return $path;
    }

    /**
     * PHASE-2.4: record every generation attempt for history/cost tracking.
     * Linked to its template/mockup once the user saves it.
     */
    protected function recordGeneration(string $type, string $path): void
    {
        try {
            \App\Models\AiGeneration::create([
                'type' => $type,
                'model' => setting('mockup_ai_model', self::DEFAULT_MODEL),
                'image_path' => $path,
                'user_id' => auth()->id(),
                'cost_estimate' => (float) setting('mockup_ai_cost_per_image', 0.04),
            ]);
        } catch (\Throwable $e) {
            Log::warning('MockupAI: failed to record generation', ['error' => $e->getMessage()]);
        }
    }

    // ── Prompt builders ──────────────────────────────────────────

    protected function buildTemplatePrompt(array $options, bool $hasReference): string
    {
        $product = $options['product_type'] === 'other' && !empty($options['custom_product'])
            ? $options['custom_product']
            : str_replace('_', ' ', $options['product_type']);

        $theme = self::THEME_PRESETS[$options['theme'] ?? 'studio'] ?? self::THEME_PRESETS['studio'];
        $size = self::SIZE_PRESETS[$options['size'] ?? 'square'] ?? self::SIZE_PRESETS['square'];
        $presentation = self::PRESENTATION_PRESETS[$options['presentation'] ?? 'product_only'] ?? self::PRESENTATION_PRESETS['product_only'];
        $angle = self::ANGLE_PRESETS[$options['angle'] ?? 'front'] ?? self::ANGLE_PRESETS['front'];
        $lighting = self::LIGHTING_PRESETS[$options['lighting'] ?? 'soft'] ?? self::LIGHTING_PRESETS['soft'];
        $views = self::VIEW_PRESETS[$options['views'] ?? 'single'] ?? self::VIEW_PRESETS['single'];

        $lines = [];
        $lines[] = "Create {$size} professional e-commerce mockup image of a {$product}.";

        if ($hasReference) {
            $lines[] = "I have attached a reference photo of the EXACT physical product. Extract the product from that photo and RE-STAGE it in the brand-new scene described below. Do NOT reuse or copy the reference photo's background, framing, lighting or composition — build a completely new professional shot around the same product.";
            $lines[] = "PRODUCT FIDELITY (critical): the product itself must stay 100% true to the reference — identical colour, design, fabric, weave, texture, stitching, seams, shape, proportions, hardware and material. Do NOT redesign, recolor, simplify or \"improve\" the product in any way. Only the scene, camera angle, lighting and presentation may change.";
        }

        $lines[] = "Scene / backdrop: {$theme}.";
        $lines[] = "Presentation: {$presentation}.";
        $lines[] = "Camera: {$angle}.";
        $lines[] = "Lighting: {$lighting}.";
        $lines[] = "Views: {$views}.";
        $lines[] = "If the presentation implies a specific camera position (e.g. a flat lay is shot top-down), the presentation takes priority over the camera angle.";

        if (!empty($options['color_scheme'])) {
            $lines[] = "Color & mood of the scene: {$options['color_scheme']}.";
        }

        $placements = !empty($options['placements'])
            ? $options['placements']
            : 'the natural primary branding position for this product';
        $coverage = self::PLACEHOLDER_COVERAGE[$options['logo_coverage'] ?? 'large'] ?? self::PLACEHOLDER_COVERAGE['large'];
        $lines[] = "Branding placeholder: render a simple, flat, clearly visible placeholder logo that says exactly \"YOUR LOGO\" in plain dark text inside a thin rectangular outline, placed at: {$placements}.";
        $lines[] = "PLACEHOLDER SIZE: make the placeholder {$coverage}. Do NOT render a tiny token placeholder — it must be big and prominent so it clearly marks the full print area. Where multiple placements are described (e.g. small pocket + large back), size each one to its role, but never smaller than clearly visible.";
        $lines[] = "The placeholder must look like a printed/embroidered brand mark on the product surface, following the fabric/material contours realistically. Do not add any other text, watermarks or brand names anywhere in the image.";

        if (!empty($options['style_notes'])) {
            $lines[] = "Additional style instructions: {$options['style_notes']}.";
        }

        $lines[] = "The final image must be photorealistic, high resolution, clean and ready to use as a reusable product mockup template.";

        return implode("\n", $lines);
    }

    /**
     * Logo size presets: how much of the product's printable area the
     * applied logo should cover.
     */
    public const LOGO_SIZES = [
        'small'  => 'a chest/pocket mark — the logo width must span AT LEAST 40% of the product\'s visible front width (clearly visible, not a tiny token)',
        'medium' => 'a standard merchandise print — the logo width must span AT LEAST 70% of the product\'s visible front width, large and prominent',
        'large'  => 'a giant full-panel print — the logo must fill the ENTIRE printable front panel of the product, spanning as close to 100% of the product\'s visible front width as possible (edge to edge, from near the top to near the bottom of the print area), as big as it can physically be while staying on the product surface',
    ];

    protected function buildMockupPrompt(MockupTemplate $template, ?string $instructions, string $logoSize = 'medium'): string
    {
        $sizeRule = self::LOGO_SIZES[$logoSize] ?? self::LOGO_SIZES['medium'];

        $lines = [];
        $lines[] = "The first attached image is a product mockup template that contains one or more placeholder logos reading \"YOUR LOGO\".";
        $lines[] = "The second attached image is the customer's real logo/branding.";
        $lines[] = "Replace EVERY placeholder \"YOUR LOGO\" mark on the product with the customer's logo from the second image.";
        $lines[] = "LOGO SIZE (very important): apply the logo as {$sizeRule}. Use the placeholder ONLY to locate WHERE the branding goes — completely IGNORE the placeholder's size. The placeholder is usually much smaller than the final logo should be; scale the customer's logo UP to meet the size rule above, even if that means it is several times larger than the placeholder box. Make the logo big and prominent.";
        $lines[] = "LOGO POSITION: center the logo on the product's natural print area at each placeholder location, visually balanced and straight (aligned with the product, not tilted), at the height a professional garment/merch printer would place it.";
        $lines[] = "The logo must be applied realistically: follow the surface contours, fabric folds, lighting and perspective of the product, as a high-quality print or embroidery would look.";
        $lines[] = "CRITICAL: Everything else in the image must remain EXACTLY as it is — the product, its color, fabric, texture, the background, lighting and composition must not change in any way. Only the placeholder branding is replaced.";
        $lines[] = "Preserve the customer logo's own colors, proportions and design faithfully; do not restyle, recolor, stretch or distort it beyond realistic surface application. Render it crisp and sharp.";

        if ($template->placements) {
            $lines[] = "For reference, the placeholder positions on this template are: {$template->placements}.";
        }

        if ($instructions) {
            $lines[] = "Additional instructions: {$instructions}.";
        }

        return implode("\n", $lines);
    }

    // ── OpenRouter call ──────────────────────────────────────────

    /**
     * Call OpenRouter with an image-output model and return the generated
     * image as binary data.
     *
     * @param string $prompt
     * @param array $imagePaths  paths on the public disk to attach as inputs
     * @return string binary image data
     * @throws \RuntimeException on any failure
     */
    protected function generateImage(string $prompt, array $imagePaths = []): string
    {
        $apiKey = setting('openrouter_api_key', env('OPENROUTER_API_KEY'));
        if (empty($apiKey)) {
            throw new \RuntimeException('OpenRouter API key is not configured. Add it in Settings → Integrations.');
        }

        $content = [['type' => 'text', 'text' => $prompt]];

        foreach ($imagePaths as $path) {
            $disk = Storage::disk('public');
            if (!$disk->exists($path)) {
                throw new \RuntimeException("Input image not found: {$path}");
            }
            $mime = $disk->mimeType($path) ?: 'image/png';
            $content[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => "data:{$mime};base64," . base64_encode($disk->get($path)),
                ],
            ];
        }

        $model = setting('mockup_ai_model', self::DEFAULT_MODEL);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'HTTP-Referer' => url('/'),
            'X-Title' => 'Chhito Pasal',
        ])
            ->timeout(120)
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $content],
                ],
                'modalities' => ['image', 'text'],
            ]);

        if ($response->failed()) {
            Log::error('MockupAI: OpenRouter API error', ['status' => $response->status(), 'body' => substr($response->body(), 0, 2000)]);
            throw new \RuntimeException('AI provider error: ' . ($response->json('error.message') ?? ('HTTP ' . $response->status())));
        }

        $dataUri = $response->json('choices.0.message.images.0.image_url.url');

        if (empty($dataUri)) {
            $text = $response->json('choices.0.message.content');
            Log::warning('MockupAI: no image in response', ['content' => is_string($text) ? substr($text, 0, 500) : $text]);
            throw new \RuntimeException('The AI did not return an image. ' . (is_string($text) && $text !== '' ? 'Model said: ' . substr($text, 0, 300) : 'Try again or adjust your instructions.'));
        }

        if (!preg_match('/^data:image\/\w+;base64,/', $dataUri)) {
            throw new \RuntimeException('The AI returned an unexpected image format.');
        }

        $binary = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $dataUri), true);
        if ($binary === false || @getimagesizefromstring($binary) === false) {
            throw new \RuntimeException('The AI returned invalid image data.');
        }

        return $binary;
    }
}
