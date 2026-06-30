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

        // Filter by product type tag
        if ($request->filled('product_type')) {
            $query->whereJsonContains('tags', $request->get('product_type'));
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->get('search') . '%');
        }

        $mockups = $query->paginate(24);

        // Also aggregate order-level mockups that don't have a library record yet
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

        $templates = MockupTemplate::all();

        return view('mockups.index', compact('mockups', 'orderMockups', 'templates'));
    }

    /**
     * Store a newly generated mockup from the canvas.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'title' => 'required|string|max:255',
            'template_id' => 'nullable|exists:mockup_templates,id',
            'tags' => 'nullable|array',
        ]);

        // Decode the base64 image
        $imageData = $request->input('image');
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        $imageData = base64_decode($imageData);

        if (!$imageData) {
            return response()->json(['success' => false, 'message' => 'Invalid image data.'], 422);
        }

        $filename = 'mockups/' . uniqid('mockup_') . '.png';
        Storage::disk('public')->put($filename, $imageData);

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
        if (Storage::disk('public')->exists($mockup->image_path)) {
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
