<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        return redirect()->route('accounting.index', ['tab' => 'purchases']);
    }

    public function store(Request $request)
    {
        if (in_array(auth()->user()->role, ['operational_staff'])) {
            return redirect()->route('dashboard')->with('error', 'Access Denied.');
        }

        $validated = $request->validate([
            'supplier_name' => 'nullable|string|max:255',
            'party_id' => 'nullable|exists:parties,id',
            'reference_no' => 'nullable|string',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('purchases', 'public');
        }

        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $totalAmount += $item['quantity'] * $item['unit_cost'];
        }

        if (empty($validated['supplier_name']) && !empty($validated['party_id'])) {
            $party = \App\Models\Party::find($validated['party_id']);
            $validated['supplier_name'] = $party ? $party->name : 'Unknown Supplier';
        } elseif (empty($validated['supplier_name'])) {
            $validated['supplier_name'] = 'Unknown Supplier';
        }

        DB::transaction(function () use ($validated, $attachmentPath, $totalAmount) {
            $purchase = \App\Models\Purchase::create([
                'supplier_name' => $validated['supplier_name'],
                'party_id' => $validated['party_id'] ?? null,
                'reference_no' => $validated['reference_no'],
                'date' => $validated['date'],
                'notes' => $validated['notes'],
                'total_amount' => $totalAmount,
                'status' => 'completed',
                'payment_status' => 'unpaid',
                'attachment_path' => $attachmentPath,
            ]);

            foreach ($validated['items'] as $item) {
                $purchase->items()->create($item);

                $product = \App\Models\Product::find($item['product_id']);
                $currentTotalValue = $product->stock * $product->cost_price;
                $newValue = $item['quantity'] * $item['unit_cost'];
                $newStock = $product->stock + $item['quantity'];
                
                $newCostPrice = $newStock > 0 ? ($currentTotalValue + $newValue) / $newStock : $item['unit_cost'];

                $product->update([
                    'stock' => $newStock,
                    'cost_price' => $newCostPrice
                ]);
            }
        });

        return redirect()->route('accounting.index', ['tab' => 'purchases'])->with('success', 'Purchase recorded and stock updated.');
    }

    public function destroy(\App\Models\Purchase $purchase)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('purchases.index')->with('error', 'Access Denied: Only Administrators can delete purchases.');
        }

        DB::transaction(function () use ($purchase) {
            // Reverse all payment transactions
            $transactions = \App\Models\Transaction::where('reference_type', 'Purchase')
                ->where('reference_id', $purchase->id)->get();
            foreach ($transactions as $tx) {
                $account = \App\Models\Account::find($tx->account_id);
                if ($account) {
                    $account->increment('balance', $tx->amount);
                }
                $tx->delete();
            }

            // Revert stock before deleting
            foreach ($purchase->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->decrement('stock', $item->quantity);
                }
            }

            $purchase->delete();
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase deleted, stock reverted, and transactions reversed.');
    }

    public function updateAmount(Request $request, \App\Models\Purchase $purchase)
    {
        $request->validate(['total_amount' => 'required|numeric|min:0']);
        
        $oldAmount = $purchase->total_amount;
        $purchase->update(['total_amount' => $request->total_amount]);

        $purchase->logActivity('updated_amount', [
            'old_amount' => $oldAmount,
            'new_amount' => $request->total_amount
        ]);

        return back()->with('success', 'Purchase amount updated successfully.');
    }
}
