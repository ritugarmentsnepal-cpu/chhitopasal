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
        // PERF-BUG-01: Paginate to prevent memory issues with large catalogs
        $products = Product::with('category')->latest()->paginate(20);
        $categories = Category::all();
        return view('products.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('products')) {
            return redirect()->route('products.index')->with('error', 'Access Denied: You do not have permission to create products.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'weight_grams' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'required_without:ai_thumbnail_url|image|mimes:jpeg,png,jpg,webp,gif|max:20480', // Thumbnail (20MB max)
            'ai_thumbnail_url' => 'nullable|url',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:20480', // Gallery (20MB max)
            'video' => 'nullable|mimes:mp4,webm,mov|max:102400', // Max 100MB video
            'bundles' => 'nullable|array',
            'bundles.*.qty' => 'required_with:bundles|integer|min:1',
            'bundles.*.price' => 'required_with:bundles|numeric|min:0',
            'bundle_only' => 'nullable|boolean',
            'color_options' => 'nullable|string|max:500',
            'size_options' => 'nullable|string|max:500',
        ]);

        $imagePath = null;
        if (!empty($validated['ai_thumbnail_url'])) {
            try {
                $contents = file_get_contents($validated['ai_thumbnail_url']);
                if ($contents) {
                    $name = 'products/thumbnails/' . Str::random(40) . '.jpg';
                    Storage::disk('public')->put($name, $contents);
                    $imagePath = $name;
                }
            } catch (\Exception $e) {}
        }
        
        if (!$imagePath && $request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products/thumbnails', 'public');
        }

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
            'bundle_only' => !empty($validated['bundles']) ? ($validated['bundle_only'] ?? false) : false,
            'color_options' => !empty($validated['color_options']) ? array_map('trim', explode(',', $validated['color_options'])) : null,
            'size_options' => !empty($validated['size_options']) ? array_map('trim', explode(',', $validated['size_options'])) : null,
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product)
    {
        if (!auth()->user()->hasPermission('products')) {
            return redirect()->route('products.index')->with('error', 'Access Denied: You do not have permission to edit products.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'weight_grams' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:20480',
            'ai_thumbnail_url' => 'nullable|url',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:20480',
            'video' => 'nullable|mimes:mp4,webm,mov|max:102400',
            'bundles' => 'nullable|array',
            'bundles.*.qty' => 'required_with:bundles|integer|min:1',
            'bundles.*.price' => 'required_with:bundles|numeric|min:0',
            'bundle_only' => 'nullable|boolean',
            'color_options' => 'nullable|string|max:500',
            'size_options' => 'nullable|string|max:500',
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
            'bundle_only' => !empty($validated['bundles']) ? ($validated['bundle_only'] ?? false) : false,
            'color_options' => !empty($validated['color_options']) ? array_map('trim', explode(',', $validated['color_options'])) : null,
            'size_options' => !empty($validated['size_options']) ? array_map('trim', explode(',', $validated['size_options'])) : null,
        ];

        if (!empty($validated['ai_thumbnail_url'])) {
            try {
                $contents = file_get_contents($validated['ai_thumbnail_url']);
                if ($contents) {
                    if ($product->image_path) {
                        Storage::disk('public')->delete($product->image_path);
                    }
                    $name = 'products/thumbnails/' . Str::random(40) . '.jpg';
                    Storage::disk('public')->put($name, $contents);
                    $data['image_path'] = $name;
                }
            } catch (\Exception $e) {}
        } elseif ($request->hasFile('image')) {
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
        if (!auth()->user()->hasPermission('products')) {
            return redirect()->route('products.index')->with('error', 'Access Denied: You do not have permission to delete products.');
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
