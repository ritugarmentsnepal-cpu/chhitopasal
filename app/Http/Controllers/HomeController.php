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
        $products = Product::with('category')->latest()->get()->makeHidden(['cost_price', 'created_at', 'updated_at']);
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('welcome', compact('products', 'categories', 'settings'));
    }

    public function show($slug)
    {
        $product = Product::with('category')->where('slug', $slug)->firstOrFail();
        $products = Product::with('category')->latest()->get();
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('product.show', compact('product', 'products', 'settings'));
    }
}
