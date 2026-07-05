<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\PathaoService;
use App\Services\OrderService;
use App\SystemAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * PHASE-1.5: Custom print orders (creation, production pipeline,
 * design/mockup files), split from OrderController.
 */
class CustomPrintController extends Controller
{
    public function storeCustomPrint(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'product_id' => 'required|exists:products,id',
            'total_quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'print_method' => 'required|string|in:dtf,screen_print',
            'print_positions' => 'required|array|min:1',
            'print_positions.*' => 'string|in:front,back,left_sleeve,right_sleeve,pocket',
            'design_files' => 'nullable|array',
            'design_files.*' => 'nullable|file|max:20480',
            'design_notes' => 'nullable|string|max:2000',
            'estimated_delivery_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000',
            'size_breakdown' => 'nullable|array',
            'size_breakdown.*' => 'integer|min:0',
            'custom_sizes' => 'nullable|string|max:500',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            $product = Product::findOrFail($validated['product_id']);

            // Handle design files upload mapping to print positions
            $designFilesPaths = null;
            if ($request->hasFile('design_files')) {
                $designFilesPaths = [];
                foreach ($request->file('design_files') as $position => $file) {
                    if ($file) {
                        $designFilesPaths[$position] = $file->store('custom-prints', 'public');
                    }
                }
            }

            // Build size breakdown — merge standard sizes with custom sizes
            $sizeBreakdown = collect($validated['size_breakdown'] ?? [])
                ->filter(fn($qty) => $qty > 0)
                ->toArray();

            // Parse custom sizes (format: "4XL:5, 5XL:3")
            if (!empty($validated['custom_sizes'])) {
                $customParts = explode(',', $validated['custom_sizes']);
                foreach ($customParts as $part) {
                    $part = trim($part);
                    if (str_contains($part, ':')) {
                        [$size, $qty] = explode(':', $part, 2);
                        $size = trim($size);
                        $qty = (int) trim($qty);
                        if ($size && $qty > 0) {
                            $sizeBreakdown[$size] = $qty;
                        }
                    }
                }
            }

            $totalQuantity = !empty($sizeBreakdown) ? array_sum($sizeBreakdown) : (int) $validated['total_quantity'];

            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'address' => $validated['address'],
                'city' => $validated['city'] ?? null,
                'total_amount' => $validated['total_amount'],
                'advance_amount' => $validated['advance_amount'] ?? 0,
                'status' => 'pending',
                'source' => 'manual',
                'order_type' => 'custom_print',
                'design_files' => $designFilesPaths,
                'design_notes' => $validated['design_notes'] ?? null,
                'print_method' => $validated['print_method'],
                'print_positions' => $validated['print_positions'],
                'production_status' => null,
                'estimated_delivery_date' => $validated['estimated_delivery_date'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $order->orderItems()->create([
                'product_id' => $product->id,
                'quantity' => $totalQuantity,
                'price_at_purchase' => $totalQuantity > 0 ? $validated['total_amount'] / $totalQuantity : 0,
                'cost_at_purchase' => $product->cost_price,
                'size_breakdown' => !empty($sizeBreakdown) ? $sizeBreakdown : null,
            ]);

            return $order;
        });

        return redirect()->route('orders.index', ['status' => 'pending', 'order_type' => 'custom_print'])
            ->with('success', 'Custom print order #' . $order->id . ' created successfully.');
    }

    public function updateProductionStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'production_status' => 'required|string|in:' . implode(',', Order::productionStatuses()),
            'production_notes' => 'nullable|string|max:2000',
        ]);

        try {
            $orderService = app(OrderService::class);
            $orderService->transitionProductionStatus(
                $order,
                $validated['production_status'],
                $validated['production_notes'] ?? null
            );

            return back()->with('success', 'Production status updated to: ' . $order->fresh()->production_status_label);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    public function updateCustomPrint(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'total_amount' => 'required|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:pending,confirmed,rejected',
            'print_method' => 'required|string|in:dtf,screen_print',
            'print_positions' => 'required|array',
            'design_files' => 'nullable|array',
            'design_files.*' => 'file|max:20480',
            'size_breakdown' => 'nullable|array',
            'custom_sizes' => 'nullable|string',
            'total_quantity' => 'required|integer|min:1',
            'estimated_delivery_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000',
            'design_notes' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($validated, $request, $order) {
            $designFilesPaths = $order->design_files ?? [];

            // Handle new file uploads, replacing existing ones for the same position
            if ($request->hasFile('design_files')) {
                foreach ($request->file('design_files') as $position => $file) {
                    if ($file->isValid()) {
                        $path = $file->store('custom_designs/' . date('Y/m'), 'public');
                        $designFilesPaths[$position] = $path;
                    }
                }
            }

            // Build size breakdown
            $sizeBreakdown = collect($validated['size_breakdown'] ?? [])
                ->filter(fn($qty) => $qty > 0)
                ->toArray();

            if (!empty($validated['custom_sizes'])) {
                $customParts = explode(',', $validated['custom_sizes']);
                foreach ($customParts as $part) {
                    $part = trim($part);
                    if (str_contains($part, ':')) {
                        [$size, $qty] = explode(':', $part, 2);
                        $size = trim($size);
                        $qty = (int) trim($qty);
                        if ($size && $qty > 0) {
                            $sizeBreakdown[$size] = $qty;
                        }
                    }
                }
            }

            $totalQuantity = !empty($sizeBreakdown) ? array_sum($sizeBreakdown) : (int) $validated['total_quantity'];

            $order->update([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'address' => $validated['address'],
                'city' => $validated['city'] ?? null,
                'total_amount' => $validated['total_amount'],
                'advance_amount' => $validated['advance_amount'] ?? 0,
                'status' => $validated['status'],
                'print_method' => $validated['print_method'],
                'print_positions' => $validated['print_positions'],
                'design_files' => $designFilesPaths,
                'estimated_delivery_date' => $validated['estimated_delivery_date'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'design_notes' => $validated['design_notes'] ?? null,
            ]);

            if ($orderItem = $order->orderItems()->first()) {
                $orderItem->update([
                    'quantity' => $totalQuantity,
                    'price_at_purchase' => $totalQuantity > 0 ? $validated['total_amount'] / $totalQuantity : 0,
                    'size_breakdown' => !empty($sizeBreakdown) ? $sizeBreakdown : null,
                ]);
            }
        });

        return back()->with('success', 'Custom print order updated successfully.');
    }

    public function saveMockup(Request $request, Order $order)
    {
        $request->validate([
            'image' => 'required|string|max:15000000', // base64 string, ~15MB cap
            'template_id' => 'nullable|exists:mockup_templates,id',
        ]);

        $base64 = $request->input('image');
        
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                return response()->json(['success' => false, 'message' => 'Invalid image type']);
            }

            $base64 = str_replace(' ', '+', $base64);
            $imageName = 'mockup_' . $order->id . '_' . time() . '.' . $type;
            $path = 'mockups/' . date('Y/m') . '/' . $imageName;

            Storage::disk('public')->put($path, base64_decode($base64));

            $mockups = $order->mockup_files ?? [];
            $mockups[] = $path;

            $order->update(['mockup_files' => $mockups]);

            // Also create a library record so it appears in the Mockup Library.
            // Persist the chosen template so the library's product-type filter works.
            \App\Models\Mockup::create([
                'title' => 'Order #' . $order->id . ' Mockup ' . count($mockups),
                'template_id' => $request->input('template_id'),
                'image_path' => $path,
                'order_id' => $order->id,
                'created_by' => auth()->id(),
                'tags' => [],
            ]);

            return response()->json(['success' => true, 'path' => $path]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid base64 format']);
    }
}
