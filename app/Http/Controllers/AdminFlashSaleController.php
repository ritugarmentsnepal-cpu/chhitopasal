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
            $query->where(function($q) {
                $q->where('is_flash_sale', true)
                  ->orWhere('bundles', 'LIKE', '%"is_flash_sale":true%')
                  ->orWhere('bundles', 'LIKE', '%"is_flash_sale":"1"%');
            });
        }

        $products = $query->latest()->paginate(20);

        $displayItems = collect();
        foreach ($products as $product) {
            if ($product->bundle_only && !empty($product->bundles) && is_array($product->bundles)) {
                foreach ($product->bundles as $index => $bundle) {
                    $virtual = clone $product;
                    $virtual->is_bundle = true;
                    $virtual->bundle_index = $index;
                    $virtual->bundle_qty = $bundle['qty'];
                    $virtual->name = $product->name . ' - Pack of ' . $bundle['qty'];
                    $virtual->regular_price = $bundle['price'];
                    $virtual->is_flash_sale = isset($bundle['is_flash_sale']) && $bundle['is_flash_sale'] ? true : false;
                    $virtual->flash_sale_price = $bundle['flash_sale_price'] ?? null;
                    // Reset ID for form handling so we can still use product->id but know it's a bundle
                    $displayItems->push($virtual);
                }
            } else {
                $product->is_bundle = false;
                $product->regular_price = $product->getRawOriginal('price');
                $displayItems->push($product);

                if (!empty($product->bundles) && is_array($product->bundles)) {
                    foreach ($product->bundles as $index => $bundle) {
                        $virtual = clone $product;
                        $virtual->is_bundle = true;
                        $virtual->bundle_index = $index;
                        $virtual->bundle_qty = $bundle['qty'];
                        $virtual->name = $product->name . ' - Pack of ' . $bundle['qty'];
                        $virtual->regular_price = $bundle['price'];
                        $virtual->is_flash_sale = isset($bundle['is_flash_sale']) && $bundle['is_flash_sale'] ? true : false;
                        $virtual->flash_sale_price = $bundle['flash_sale_price'] ?? null;
                        $displayItems->push($virtual);
                    }
                }
            }
        }

        return view('admin.flash-sales.index', compact('products', 'displayItems'));
    }

    /**
     * Update the flash sale status and price for a product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'is_flash_sale' => 'required|boolean',
            'flash_sale_price' => 'nullable|numeric|min:0',
            'bundle_index' => 'nullable|integer'
        ]);

        if ($request->has('bundle_index') && $request->bundle_index !== null) {
            $bundles = $product->bundles ?? [];
            $index = $request->bundle_index;
            if (isset($bundles[$index])) {
                $bundles[$index]['is_flash_sale'] = (bool) $validated['is_flash_sale'];
                $bundles[$index]['flash_sale_price'] = $validated['is_flash_sale'] ? $validated['flash_sale_price'] : null;
                $product->update(['bundles' => $bundles]);
            }
        } else {
            $product->update([
                'is_flash_sale' => $validated['is_flash_sale'],
                'flash_sale_price' => $validated['is_flash_sale'] ? $validated['flash_sale_price'] : null,
            ]);
        }

        return redirect()->back()->with('success', 'Flash sale status updated');
    }
}
