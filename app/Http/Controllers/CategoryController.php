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
        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);
        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'Access Denied: Only Administrators can edit categories.');
        }

        $request->validate(['name' => 'required|string|max:255']);
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
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
