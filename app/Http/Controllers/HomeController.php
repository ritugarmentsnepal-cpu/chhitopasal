<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        // FRONT-01: Hide stock count and cost_price from public JSON
        $products = Product::with('category')->latest()->get()->makeHidden(['cost_price', 'stock', 'created_at', 'updated_at']);

        // BUNDLE-ONLY: Expand bundle-only products into separate virtual cards
        $displayProducts = collect();
        foreach ($products as $product) {
            if ($product->bundle_only && !empty($product->bundles) && is_array($product->bundles)) {
                // Create a separate virtual card for each bundle
                foreach ($product->bundles as $bundle) {
                    $qty = (int) $bundle['qty'];
                    $bundlePrice = (float) $bundle['price'];
                    $virtualProduct = clone $product;
                    $virtualProduct->name = $product->name . ' - Pack of ' . $qty;
                    $virtualProduct->price = $bundlePrice;
                    // Attach bundle metadata as dynamic attributes for the frontend
                    $virtualProduct->bundle_qty = $qty;
                    $virtualProduct->bundle_price = $bundlePrice;
                    $virtualProduct->is_bundle_card = true;
                    $virtualProduct->parent_product_slug = $product->slug;
                    // Clear bundles array so the card doesn't trigger the bundle modal
                    $virtualProduct->bundles = null;
                    $displayProducts->push($virtualProduct);
                }
            } else {
                $product->is_bundle_card = false;
                $product->bundle_qty = null;
                $product->bundle_price = null;
                $product->parent_product_slug = null;
                $displayProducts->push($product);
            }
        }
        $products = $displayProducts;

        $settings = Setting::pluck('value', 'key')->toArray();
        return view('welcome', compact('products', 'categories', 'settings'));
    }


    public function show(Request $request, $slug)
    {
        // UX-02: Hide cost_price on product detail page to prevent leaking sensitive data
        $product = Product::with('category')->where('slug', $slug)->firstOrFail()
            ->makeHidden(['cost_price', 'stock', 'created_at', 'updated_at']);

        $selectedBundleQty = $request->query('bundle');
        $selectedBundle = null;

        if ($product->bundle_only && !empty($product->bundles) && is_array($product->bundles)) {
            if ($selectedBundleQty) {
                $selectedBundle = collect($product->bundles)->firstWhere('qty', $selectedBundleQty);
            }
            if (!$selectedBundle) {
                $selectedBundle = $product->bundles[0]; // default to first bundle
            }
            
            // Update the main product data to represent the selected bundle
            $product->name = $product->name . ' - Pack of ' . $selectedBundle['qty'];
            $product->price = (float) $selectedBundle['price'];
            $product->is_bundle_card = true;
            $product->bundle_qty = (int) $selectedBundle['qty'];
            $product->bundle_price = (float) $selectedBundle['price'];
        } else {
            $product->is_bundle_card = false;
        }

        $products = Product::with('category')->latest()->get()
            ->makeHidden(['cost_price', 'stock', 'created_at', 'updated_at']);
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('product.show', compact('product', 'products', 'settings', 'selectedBundle'));
    }

    public function privacyPolicy()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('privacy-policy', compact('settings'));
    }

    public function companyProfile()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('company-profile', compact('settings'));
    }
}
