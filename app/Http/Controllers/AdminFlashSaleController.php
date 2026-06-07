<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class AdminFlashSaleController extends Controller
{
    /**
     * Display a listing of products to manage flash sales.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Optional filtering by search or category if needed
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('slug', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && $request->status == 'flash_sale') {
            $query->where('is_flash_sale', true);
        }

        $products = $query->latest()->paginate(20);

        return view('admin.flash-sales.index', compact('products'));
    }

    /**
     * Update the flash sale status and price for a product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'is_flash_sale' => 'required|boolean',
            'flash_sale_price' => 'nullable|numeric|min:0'
        ]);

        $product->update([
            'is_flash_sale' => $validated['is_flash_sale'],
            'flash_sale_price' => $validated['is_flash_sale'] ? $validated['flash_sale_price'] : null,
        ]);

        return redirect()->back()->with('success', 'Flash sale status updated for ' . $product->name);
    }
}
