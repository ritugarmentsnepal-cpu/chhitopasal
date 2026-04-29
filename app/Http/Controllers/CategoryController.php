<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->latest()->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $colorOptions = null;
        $sizeOptions = null;

        if ($request->has('color_options') && $request->color_options) {
            $colorOptions = array_map('trim', explode(',', $request->color_options));
            $colorOptions = array_values(array_filter($colorOptions));
        }

        if ($request->has('size_options') && $request->size_options) {
            $sizeOptions = array_map('trim', explode(',', $request->size_options));
            $sizeOptions = array_values(array_filter($sizeOptions));
        }

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'has_color_variants' => $request->boolean('has_color_variants'),
            'has_size_variants' => $request->boolean('has_size_variants'),
            'color_options' => $colorOptions,
            'size_options' => $sizeOptions,
        ]);
        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'Access Denied: Only Administrators can edit categories.');
        }

        $request->validate(['name' => 'required|string|max:255']);

        $colorOptions = null;
        $sizeOptions = null;

        if ($request->has('color_options') && $request->color_options) {
            $colorOptions = array_map('trim', explode(',', $request->color_options));
            $colorOptions = array_values(array_filter($colorOptions));
        }

        if ($request->has('size_options') && $request->size_options) {
            $sizeOptions = array_map('trim', explode(',', $request->size_options));
            $sizeOptions = array_values(array_filter($sizeOptions));
        }

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'has_color_variants' => $request->boolean('has_color_variants'),
            'has_size_variants' => $request->boolean('has_size_variants'),
            'color_options' => $colorOptions,
            'size_options' => $sizeOptions,
        ]);
        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'Access Denied: Only Administrators can delete categories.');
        }

        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}
