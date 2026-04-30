<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->get();
        $categories = Category::all();
        return view('products.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        // SEC-MED-05: Only admins can create products
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('products.index')->with('error', 'Access Denied: Only Administrators can create products.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'weight_grams' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:2048', // Thumbnail
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'video' => 'nullable|mimes:mp4,webm,mov|max:10240', // Max 10MB video
            'bundles' => 'nullable|array',
            'bundles.*.qty' => 'required_with:bundles|integer|min:2',
            'bundles.*.price' => 'required_with:bundles|numeric|min:0',
        ]);

        $imagePath = $request->file('image')->store('products/thumbnails', 'public');

        $additionalImages = [];
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $file) {
                $additionalImages[] = $file->store('products/gallery', 'public');
            }
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('products/videos', 'public');
        }

        Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(8),
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'cost_price' => $validated['cost_price'] ?? 0,
            'weight_grams' => $validated['weight_grams'],
            'stock' => $validated['stock'],
            'image_path' => $imagePath,
            'additional_images' => $additionalImages,
            'video_path' => $videoPath,
            'bundles' => $validated['bundles'] ?? null,
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('products.index')->with('error', 'Access Denied: Only Administrators can edit products.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'weight_grams' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'video' => 'nullable|mimes:mp4,webm,mov|max:10240',
            'bundles' => 'nullable|array',
            'bundles.*.qty' => 'required_with:bundles|integer|min:2',
            'bundles.*.price' => 'required_with:bundles|numeric|min:0',
        ]);

        $data = [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'cost_price' => $validated['cost_price'] ?? $product->cost_price,
            'weight_grams' => $validated['weight_grams'],
            'stock' => $validated['stock'],
            'bundles' => $validated['bundles'] ?? null,
        ];

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products/thumbnails', 'public');
        }

        if ($request->hasFile('additional_images')) {
            // Delete old gallery
            if ($product->additional_images) {
                foreach ($product->additional_images as $oldImg) {
                    Storage::disk('public')->delete($oldImg);
                }
            }
            $newImages = [];
            foreach ($request->file('additional_images') as $file) {
                $newImages[] = $file->store('products/gallery', 'public');
            }
            $data['additional_images'] = $newImages;
        }

        if ($request->hasFile('video')) {
            if ($product->video_path) {
                Storage::disk('public')->delete($product->video_path);
            }
            $data['video_path'] = $request->file('video')->store('products/videos', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('products.index')->with('error', 'Access Denied: Only Administrators can delete products.');
        }

        try {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            if ($product->additional_images) {
                foreach ($product->additional_images as $img) {
                    Storage::disk('public')->delete($img);
                }
            }
            if ($product->video_path) {
                Storage::disk('public')->delete($product->video_path);
            }
            $product->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('products.index')->with('error', 'Cannot delete product because it is linked to existing orders.');
        }

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
