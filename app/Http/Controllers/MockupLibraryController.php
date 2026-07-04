<?php

namespace App\Http\Controllers;

use App\Models\Mockup;
use App\Models\MockupTemplate;
use App\Models\Order;
use App\Services\MockupAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MockupLibraryController extends Controller
{
    /**
     * Order statuses that mean the order is confirmed and its logo is
     * ready to be sent for printing.
     */
    protected const CONFIRMED_STATUSES = ['confirmed', 'shipped', 'delivered'];

    /**
     * Display the Mockup Studio page (mockups, templates, print logos).
     */
    public function index(Request $request)
    {
        $query = Mockup::with(['template', 'order', 'creator'])->latest();

        // Filter by source
        if ($request->get('source') === 'order') {
            $query->whereNotNull('order_id');
        } elseif ($request->get('source') === 'standalone') {
            $query->whereNull('order_id');
        }

        // Filter by product type — attribute of the base template
        if ($request->filled('product_type')) {
            $type = $request->get('product_type');
            $query->whereHas('template', function ($q) use ($type) {
                $q->where('product_type', $type);
            });
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->get('search') . '%');
        }

        $mockups = $query->paginate(24);

        $templates = MockupTemplate::latest()->get();

        // Product types present in the template library (drives the filter dropdown)
        $productTypes = MockupTemplate::query()
            ->select('product_type')
            ->distinct()
            ->orderBy('product_type')
            ->pluck('product_type');

        // Print logo library: order-linked mockups that carry a customer logo.
        // Confirmed orders are ready for print; pending ones are still waiting.
        $printLogos = Mockup::with('order')
            ->whereNotNull('logo_path')
            ->whereNotNull('order_id')
            ->latest()
            ->get();

        $readyLogos = $printLogos->filter(fn ($m) => $m->order && in_array($m->order->status, self::CONFIRMED_STATUSES))->values();
        $waitingLogos = $printLogos->filter(fn ($m) => !$m->order || !in_array($m->order->status, self::CONFIRMED_STATUSES))->values();

        return view('mockups.index', compact('mockups', 'templates', 'productTypes', 'readyLogos', 'waitingLogos'));
    }

    /**
     * AI-generate a mockup from a template + customer logo (preview step).
     * The logo is stored permanently up-front so it can be reused for
     * regeneration and later downloaded for printing.
     */
    public function generate(Request $request, MockupAiService $ai)
    {
        $request->validate([
            'template_id' => 'required|exists:mockup_templates,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            // Reuse an already-uploaded logo (regeneration / batch across templates)
            'logo_path' => 'nullable|string|max:255',
            'instructions' => 'nullable|string|max:1000',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('mockup_logos', 'public');
        } elseif ($request->filled('logo_path')) {
            $candidate = $request->input('logo_path');
            if (Str::startsWith($candidate, 'mockup_logos/') && Storage::disk('public')->exists($candidate)) {
                $logoPath = $candidate;
            }
        }

        if (!$logoPath) {
            return response()->json(['success' => false, 'message' => 'Please upload the customer logo.'], 422);
        }

        $template = MockupTemplate::findOrFail($request->input('template_id'));

        try {
            $path = $ai->generateMockupImage($template, $logoPath, $request->input('instructions'));
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => '/storage/' . $path,
            'logo_path' => $logoPath,
            'template_id' => $template->id,
        ]);
    }

    /**
     * Confirm a previously generated mockup into the library, optionally
     * linked to an order.
     */
    public function saveGenerated(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'path' => 'required|string|max:255',
            'logo_path' => 'required|string|max:255',
            'template_id' => 'nullable|exists:mockup_templates,id',
            'order_id' => 'nullable|integer|exists:orders,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $path = $request->input('path');
        $logoPath = $request->input('logo_path');

        // Only accept files this feature generated/stored itself
        if (!Str::startsWith($path, 'mockups/ai_') || !Storage::disk('public')->exists($path)) {
            return response()->json(['success' => false, 'message' => 'Generated image not found.'], 422);
        }
        if (!Str::startsWith($logoPath, 'mockup_logos/') || !Storage::disk('public')->exists($logoPath)) {
            return response()->json(['success' => false, 'message' => 'Logo file not found.'], 422);
        }

        $mockup = Mockup::create([
            'title' => $request->input('title'),
            'template_id' => $request->input('template_id'),
            'image_path' => $path,
            'logo_path' => $logoPath,
            'order_id' => $request->input('order_id'),
            'created_by' => auth()->id(),
            'tags' => $request->input('tags', []),
        ]);

        // Keep the order's own mockup list in sync so it shows on the order
        if ($mockup->order_id && $mockup->order) {
            $files = $mockup->order->mockup_files ?? [];
            $files[] = $path;
            $mockup->order->update(['mockup_files' => $files]);
        }

        return response()->json(['success' => true, 'mockup' => $mockup]);
    }

    /**
     * Delete a mockup (and its files when nothing else references them).
     */
    public function destroy(Mockup $mockup)
    {
        // Order mockups share their image path with the order's mockup_files list
        if ($mockup->order_id && $mockup->order) {
            $remaining = collect($mockup->order->mockup_files ?? [])
                ->reject(fn ($path) => $path === $mockup->image_path)
                ->values()
                ->all();
            $mockup->order->update(['mockup_files' => $remaining]);
        }

        // Only delete physical files no other mockup still references
        $imageShared = Mockup::where('image_path', $mockup->image_path)
            ->where('id', '!=', $mockup->id)
            ->exists();
        if (!$imageShared && Storage::disk('public')->exists($mockup->image_path)) {
            Storage::disk('public')->delete($mockup->image_path);
        }

        if ($mockup->logo_path) {
            $logoShared = Mockup::where('logo_path', $mockup->logo_path)
                ->where('id', '!=', $mockup->id)
                ->exists();
            if (!$logoShared && Storage::disk('public')->exists($mockup->logo_path)) {
                Storage::disk('public')->delete($mockup->logo_path);
            }
        }

        $mockup->delete();

        return back()->with('success', 'Mockup deleted successfully.');
    }

    /**
     * Download a mockup image.
     */
    public function download(Mockup $mockup)
    {
        $path = storage_path('app/public/' . $mockup->image_path);

        if (!file_exists($path)) {
            return back()->with('error', 'Mockup file not found.');
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'png';
        $name = $mockup->order_id ? "Order-{$mockup->order_id}-Mockup-{$mockup->id}" : Str::slug($mockup->title);

        return response()->download($path, $name . '.' . $ext);
    }

    /**
     * Download the customer logo used on a mockup, named by order number so
     * it's easy to locate when sending for printing.
     */
    public function downloadLogo(Mockup $mockup)
    {
        if (!$mockup->logo_path) {
            return back()->with('error', 'This mockup has no logo file.');
        }

        $path = storage_path('app/public/' . $mockup->logo_path);

        if (!file_exists($path)) {
            return back()->with('error', 'Logo file not found.');
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'png';
        $name = $mockup->order_id ? "Order-{$mockup->order_id}-Logo" : 'Logo-Mockup-' . $mockup->id;

        return response()->download($path, $name . '.' . $ext);
    }
}
