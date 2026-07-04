<?php

namespace App\Http\Controllers;

use App\Models\Mockup;
use App\Models\MockupTemplate;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MockupLibraryController extends Controller
{
    /**
     * Display the Mockup Library page.
     */
    public function index(Request $request)
    {
        // Fetch standalone mockups
        $query = Mockup::with(['template', 'order', 'creator'])->latest();

        // Filter by source
        if ($request->get('source') === 'order') {
            $query->whereNotNull('order_id');
        } elseif ($request->get('source') === 'standalone') {
            $query->whereNull('order_id');
        }

        // Filter by product type — this is an attribute of the base template,
        // so match against the linked template rather than the free-form tags.
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

        // Also aggregate legacy order-level mockups that don't have a library
        // record yet. These have no template link, so they can't be matched by
        // a product_type filter, and they aren't "standalone" — skip them when
        // either of those filters is active to keep results consistent.
        $orderMockups = collect();
        if (!$request->filled('product_type') && $request->get('source') !== 'standalone') {
            $orderMockups = Order::whereNotNull('mockup_files')
                ->where('mockup_files', '!=', '[]')
                ->whereDoesntHave('libraryMockups')
                ->with('orderItems')
                ->latest()
                ->get()
                ->flatMap(function ($order) {
                    $files = is_array($order->mockup_files) ? $order->mockup_files : [];
                    return collect($files)->map(function ($path, $index) use ($order) {
                        return (object) [
                            'id' => null,
                            'title' => "Order #{$order->id} Mockup " . ($index + 1),
                            'image_path' => $path,
                            'order_id' => $order->id,
                            'order' => $order,
                            'created_at' => $order->updated_at,
                            'is_order_inline' => true,
                        ];
                    });
                });
        }

        $templates = MockupTemplate::all();

        return view('mockups.index', compact('mockups', 'orderMockups', 'templates'));
    }

    /**
     * Store a newly generated mockup from the canvas.
     */
    public function store(Request $request)
    {
        $request->validate([
            // ~15MB base64 cap (a 2x PNG export can be large, but this bounds abuse)
            'image' => 'required|string|max:15000000',
            'title' => 'required|string|max:255',
            'template_id' => 'nullable|exists:mockup_templates,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        // Require a proper data-URI and only accept the formats the canvas exports.
        $imageData = $request->input('image');
        if (!preg_match('/^data:image\/(png|jpe?g);base64,/', $imageData, $match)) {
            return response()->json(['success' => false, 'message' => 'Invalid image format.'], 422);
        }
        $ext = strtolower($match[1]) === 'png' ? 'png' : 'jpg';

        // Decode the base64 payload strictly, then confirm it is really an image.
        $binary = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageData), true);
        if ($binary === false || @getimagesizefromstring($binary) === false) {
            return response()->json(['success' => false, 'message' => 'Invalid image data.'], 422);
        }

        $filename = 'mockups/' . uniqid('mockup_') . '.' . $ext;
        Storage::disk('public')->put($filename, $binary);

        $mockup = Mockup::create([
            'title' => $request->input('title'),
            'template_id' => $request->input('template_id'),
            'image_path' => $filename,
            'order_id' => null,
            'created_by' => auth()->id(),
            'tags' => $request->input('tags', []),
        ]);

        return response()->json([
            'success' => true,
            'mockup' => $mockup,
            'url' => Storage::url($filename),
        ]);
    }

    /**
     * Delete a standalone mockup.
     */
    public function destroy(Mockup $mockup)
    {
        // Order mockups share their image file with the order's mockup_files list.
        // Remove this path from the linked order so we don't leave a broken reference.
        if ($mockup->order_id && $mockup->order) {
            $remaining = collect($mockup->order->mockup_files ?? [])
                ->reject(fn ($path) => $path === $mockup->image_path)
                ->values()
                ->all();
            $mockup->order->update(['mockup_files' => $remaining]);
        }

        // Only delete the physical file if no other mockup record still references it.
        $sharedByOthers = Mockup::where('image_path', $mockup->image_path)
            ->where('id', '!=', $mockup->id)
            ->exists();

        if (!$sharedByOthers && Storage::disk('public')->exists($mockup->image_path)) {
            Storage::disk('public')->delete($mockup->image_path);
        }

        $mockup->delete();

        return back()->with('success', 'Mockup deleted successfully.');
    }

    /**
     * Download a mockup as PNG.
     */
    public function download(Mockup $mockup)
    {
        $path = storage_path('app/public/' . $mockup->image_path);

        if (!file_exists($path)) {
            return back()->with('error', 'Mockup file not found.');
        }

        return response()->download($path, $mockup->title . '.png');
    }
}
