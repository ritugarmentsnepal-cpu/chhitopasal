<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index()
    {
        return redirect()->route('accounting.index', ['tab' => 'expenses']);
    }

    public function store(Request $request)
    {
        if (in_array(auth()->user()->role, ['operational_staff'])) {
            return redirect()->route('dashboard')->with('error', 'Access Denied.');
        }

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'description' => 'nullable|string',
            'reference_no' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('expenses', 'public');
        }

        DB::transaction(function () use ($validated, $attachmentPath) {
            $expense = \App\Models\Expense::create([
                'expense_category_id' => $validated['expense_category_id'],
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'description' => $validated['description'],
                'reference_no' => $validated['reference_no'],
                'attachment_path' => $attachmentPath,
            ]);

            $account = \App\Models\Account::findOrFail($validated['account_id']);
            \App\Models\Transaction::create([
                'account_id' => $account->id,
                'type' => 'out',
                'amount' => $validated['amount'],
                'reference_type' => 'Expense',
                'reference_id' => $expense->id,
                'date' => $validated['date'],
                'notes' => 'Expense: ' . ($validated['description'] ?: 'General Expense')
            ]);
            
            $account->decrement('balance', $validated['amount']);
        });

        return redirect()->route('accounting.index', ['tab' => 'expenses'])->with('success', 'Expense recorded successfully.');
    }

    public function update(Request $request, \App\Models\Expense $expense)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('expenses.index')->with('error', 'Access Denied: Only Administrators can edit expenses.');
        }

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'reference_no' => 'nullable|string',
        ]);

        $oldAmount = $expense->amount;
        $newAmount = $validated['amount'];

        DB::transaction(function () use ($expense, $validated, $oldAmount, $newAmount) {
            $expense->update($validated);

            if ($oldAmount != $newAmount) {
                $tx = \App\Models\Transaction::where('reference_type', 'Expense')
                    ->where('reference_id', $expense->id)->first();
                if ($tx) {
                    $account = \App\Models\Account::find($tx->account_id);
                    if ($account) {
                        $account->increment('balance', $oldAmount);
                        $account->decrement('balance', $newAmount);
                    }
                    $tx->update(['amount' => $newAmount]);
                }
            }
        });

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(\App\Models\Expense $expense)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('expenses.index')->with('error', 'Access Denied: Only Administrators can delete expenses.');
        }

        DB::transaction(function () use ($expense) {
            $tx = \App\Models\Transaction::where('reference_type', 'Expense')
                ->where('reference_id', $expense->id)->first();
            if ($tx) {
                $account = \App\Models\Account::find($tx->account_id);
                if ($account) {
                    $account->increment('balance', $tx->amount);
                }
                $tx->delete();
            }

            $expense->delete();
        });

        return redirect()->route('expenses.index')->with('success', 'Expense deleted and transaction reversed.');
    }
}
