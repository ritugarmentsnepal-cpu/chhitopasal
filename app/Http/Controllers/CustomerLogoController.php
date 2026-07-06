<?php

namespace App\Http\Controllers;

use App\Models\CustomerLogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * PHASE-2.1: Customer Logo Library — named, customer-linked, reusable logos.
 */
class CustomerLogoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $path = $request->file('logo')->store('mockup_logos', 'public');

        $logo = CustomerLogo::create([
            'name' => $request->input('name'),
            'customer_name' => $request->input('customer_name'),
            'customer_phone' => $request->input('customer_phone'),
            'file_path' => $path,
            'created_by' => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'logo' => $logo]);
        }

        return back()->with('success', 'Logo added to the library.');
    }

    public function update(Request $request, CustomerLogo $logo)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
        ]);

        $logo->update($request->only(['name', 'customer_name', 'customer_phone']));

        return back()->with('success', 'Logo details updated.');
    }

    public function destroy(CustomerLogo $logo)
    {
        // Keep mockups intact: they retain their own logo_path copy reference.
        // Only delete the physical file if no mockup still points at it.
        $usedByMockups = $logo->mockups()->exists()
            || \App\Models\Mockup::where('logo_path', $logo->file_path)->exists();

        if (!$usedByMockups && Storage::disk('public')->exists($logo->file_path)) {
            Storage::disk('public')->delete($logo->file_path);
        }

        $logo->delete();

        return back()->with('success', 'Logo removed from the library.');
    }
}
