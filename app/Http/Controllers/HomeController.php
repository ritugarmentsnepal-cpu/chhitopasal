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
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('welcome', compact('products', 'categories', 'settings'));
    }

    public function show($slug)
    {
        // UX-02: Hide cost_price on product detail page to prevent leaking sensitive data
        $product = Product::with('category')->where('slug', $slug)->firstOrFail()
            ->makeHidden(['cost_price', 'stock', 'created_at', 'updated_at']);
        $products = Product::with('category')->latest()->get()
            ->makeHidden(['cost_price', 'stock', 'created_at', 'updated_at']);
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('product.show', compact('product', 'products', 'settings'));
    }
}
