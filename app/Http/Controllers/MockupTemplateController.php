<?php

namespace App\Http\Controllers;

use App\Models\MockupTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MockupTemplateController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'image' => 'required|image|max:10240',
        ]);

        $path = $request->file('image')->store('mockup_templates', 'public');

        MockupTemplate::create([
            'name' => $request->name,
            'product_type' => $request->product_type,
            'image_path' => $path,
        ]);

        return back()->with('success', 'Mockup template added successfully.');
    }

    public function destroy(MockupTemplate $template)
    {
        if (Storage::disk('public')->exists($template->image_path)) {
            Storage::disk('public')->delete($template->image_path);
        }
        $template->delete();

        return back()->with('success', 'Mockup template deleted successfully.');
    }
}
