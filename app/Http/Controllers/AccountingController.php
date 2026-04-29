<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    public function index(Request $request)
    {
        if (in_array(auth()->user()->role, ['operational_staff'])) {
            return redirect()->route('dashboard')->with('error', 'Access Denied.');
        }

        $tab = $request->query('tab', 'dashboard');
        $allowedTabs = ['dashboard', 'pos', 'invoices', 'returns', 'parties', 'purchases', 'expenses', 'banking', 'inventory', 'reports', 'activity'];
        if (!in_array($tab, $allowedTabs)) {
            $tab = 'dashboard';
        }
        $data = [];

        if ($tab === 'dashboard') {
            $data['revenue'] = \App\Models\Order::where('status', 'delivered')->sum('total_amount');
            $data['pendingRevenue'] = \App\Models\Order::whereIn('status', ['pending', 'confirmed', 'shipped'])->sum('total_amount');
            
            $data['cogs'] = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', 'delivered')
                ->sum(DB::raw('order_items.quantity * order_items.cost_at_purchase'));
            $data['expenses'] = \App\Models\Expense::sum('amount');
            $data['purchases'] = \App\Models\Purchase::sum('total_amount');
            $data['grossProfit'] = $data['revenue'] - $data['cogs'];
            $data['netProfit'] = $data['grossProfit'] - $data['expenses'];
            
            // Ledger Balances
            $data['totalCash'] = \App\Models\Account::where('type', 'cash')->sum('balance');
            $data['totalBank'] = \App\Models\Account::where('type', 'bank')->sum('balance');
            
            // Quick Actions Data
            $data['categories'] = \App\Models\ExpenseCategory::all();
            $data['accounts'] = \App\Models\Account::all();
            $data['parties'] = \App\Models\Party::all();
        } elseif ($tab === 'pos') {
            $data['products'] = \App\Models\Product::where('stock', '>', 0)->get();
        } elseif ($tab === 'invoices') {
            $data['orders'] = \App\Models\Order::with('orderItems')->latest()->paginate(20);
        } elseif ($tab === 'returns') {
            $data['returned_orders'] = \App\Models\Order::with('orderItems')
                ->whereIn('status', ['failed', 'rejected', 'return_delivered'])
                ->latest()->get();
            $data['pathao_clearing'] = \App\Models\Account::where('name', 'Pathao Clearing')->first();
            $data['accounts'] = \App\Models\Account::all();
        } elseif ($tab === 'parties') {
            $data['parties'] = \App\Models\Party::all();
        } elseif ($tab === 'purchases') {
            $data['purchases'] = \App\Models\Purchase::with('party')->latest()->get();
            $data['parties'] = \App\Models\Party::whereIn('type', ['supplier'])->get();
        } elseif ($tab === 'expenses') {
            $data['expenses'] = \App\Models\Expense::with('category')->latest()->get();
            $data['categories'] = \App\Models\ExpenseCategory::all();
            $data['accounts'] = \App\Models\Account::all();
        } elseif ($tab === 'banking') {
            $data['accounts'] = \App\Models\Account::all();
            $data['transactions'] = \App\Models\Transaction::with(['account', 'party'])->latest()->get();
        } elseif ($tab === 'activity') {
            $data['logs'] = \App\Models\ActivityLog::with('user')->latest()->paginate(50);
        } elseif ($tab === 'inventory') {
            $data['products'] = \App\Models\Product::with('category')->get();
            $data['inventory_logs'] = \App\Models\ActivityLog::with('user')
                                        ->where('model_type', \App\Models\Product::class)
                                        ->latest()->paginate(20);
        } elseif ($tab === 'reports') {
            $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->query('end_date', now()->toDateString());
            $partyId = $request->query('party_id');
            $accountId = $request->query('account_id');
            $reportType = $request->query('report_type', 'pl'); // pl or ledger

            $data['start_date'] = $startDate;
            $data['end_date'] = $endDate;
            $data['report_type'] = $reportType;
            $data['parties'] = \App\Models\Party::all();
            $data['accounts'] = \App\Models\Account::all();

            if ($reportType === 'pl') {
                $deliveredOrders = \App\Models\Order::with('orderItems')->where('status', 'delivered')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->get();
                $data['pl_revenue'] = $deliveredOrders->sum('total_amount');
                
                $cogs = 0;
                foreach ($deliveredOrders as $order) {
                    foreach ($order->orderItems as $item) {
                        $cogs += ($item->quantity * $item->cost_at_purchase);
                    }
                }
                $data['pl_cogs'] = $cogs;
                $data['pl_expenses'] = \App\Models\Expense::whereBetween('date', [$startDate, $endDate])->sum('amount');
                $data['pl_gross'] = $data['pl_revenue'] - $data['pl_cogs'];
                $data['pl_net'] = $data['pl_gross'] - $data['pl_expenses'];
            } else {
                // Ledger
                $query = \App\Models\Transaction::with(['account', 'party'])
                    ->whereBetween('date', [$startDate, $endDate]);
                
                if ($partyId) {
                    $query->where('party_id', $partyId);
                    $data['selected_party'] = $partyId;
                }
                if ($accountId) {
                    $query->where('account_id', $accountId);
                    $data['selected_account'] = $accountId;
                }

                $data['ledger_transactions'] = $query->orderBy('date', 'asc')->get();
            }
        }

        return view('accounting.index', compact('tab', 'data'));
    }

    public function syncPathao()
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('accounting.index', ['tab' => 'banking'])->with('error', 'Access Denied: Only Administrators can run Pathao settlement sync.');
        }

        $orders = \App\Models\Order::where('status', 'delivered')
                    ->where('payment_status', 'unpaid')
                    ->whereNotNull('pathao_consignment_id')
                    ->get();

        $clearingAccount = \App\Models\Account::where('name', 'Pathao Clearing')->first();
        $syncedCount = 0;

        if ($clearingAccount) {
            DB::transaction(function () use ($orders, $clearingAccount, &$syncedCount) {
                foreach ($orders as $order) {
                    // Guard: prevent duplicate settlement transactions
                    $alreadySynced = \App\Models\Transaction::where('reference_type', 'Order')
                        ->where('reference_id', $order->id)
                        ->where('account_id', $clearingAccount->id)
                        ->where('notes', 'like', '%Auto-Sync Pathao Settlement%')
                        ->exists();

                    if ($alreadySynced) continue;

                    $order->update([
                        'payment_status' => 'paid',
                        'paid_amount' => $order->total_amount
                    ]);

                    \App\Models\Transaction::create([
                        'account_id' => $clearingAccount->id,
                        'type' => 'in',
                        'amount' => $order->total_amount,
                        'reference_type' => 'Order',
                        'reference_id' => $order->id,
                        'date' => now(),
                        'notes' => 'Auto-Sync Pathao Settlement (Consignment: ' . $order->pathao_consignment_id . ')'
                    ]);

                    $clearingAccount->increment('balance', $order->total_amount);
                    $syncedCount++;
                }
            });
        }

        return redirect()->route('accounting.index', ['tab' => 'banking'])->with('success', "Synced {$syncedCount} Pathao settlements successfully.");
    }

    public function payPurchase(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($validated) {
            $purchase = \App\Models\Purchase::findOrFail($validated['purchase_id']);
            $account = \App\Models\Account::findOrFail($validated['account_id']);

            \App\Models\Transaction::create([
                'account_id' => $account->id,
                'type' => 'out',
                'amount' => $validated['amount'],
                'reference_type' => 'Purchase',
                'reference_id' => $purchase->id,
                'party_id' => $purchase->party_id,
                'date' => now(),
                'notes' => $validated['notes']
            ]);

            $account->decrement('balance', $validated['amount']);
            $purchase->increment('paid_amount', $validated['amount']);
            
            $purchase->refresh();
            if ($purchase->paid_amount >= $purchase->total_amount) {
                $purchase->update(['payment_status' => 'paid']);
            } elseif ($purchase->paid_amount > 0) {
                $purchase->update(['payment_status' => 'partial']);
            }
        });

        return redirect()->route('accounting.index', ['tab' => 'purchases'])->with('success', 'Payment recorded successfully.');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        \App\Models\ExpenseCategory::create($validated);

        return redirect()->route('accounting.index', ['tab' => 'expenses'])->with('success', 'Expense Category added successfully.');
    }

    public function storeParty(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:customer,supplier,pathao',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'opening_balance' => 'nullable|numeric',
            'current_balance' => 'nullable|numeric',
        ]);

        \App\Models\Party::create($validated);

        return redirect()->route('accounting.index', ['tab' => 'parties'])->with('success', 'Party added successfully.');
    }

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'party_id' => 'nullable|exists:parties,id',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($validated) {
            $account = \App\Models\Account::findOrFail($validated['account_id']);

            \App\Models\Transaction::create([
                'account_id' => $account->id,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'party_id' => $validated['party_id'] ?? null,
                'date' => now(),
                'notes' => $validated['notes']
            ]);

            if ($validated['type'] === 'in') {
                $account->increment('balance', $validated['amount']);
            } else {
                $account->decrement('balance', $validated['amount']);
            }
        });

        return redirect()->route('accounting.index', ['tab' => 'banking'])->with('success', 'Manual transaction recorded successfully.');
    }

    public function adjustStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:add,deduct',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $product = \App\Models\Product::findOrFail($validated['product_id']);
        $oldStock = $product->stock;

        if ($validated['type'] === 'add') {
            $product->increment('stock', $validated['quantity']);
        } else {
            // Guard: prevent stock from going negative
            \App\Models\Product::where('id', $product->id)->where('stock', '>=', $validated['quantity'])->decrement('stock', $validated['quantity']);
            $product->refresh();
        }

        $actionName = $validated['type'] === 'add' ? 'stock_adjusted_add' : 'stock_adjusted_deduct';
        
        $product->logActivity($actionName, [
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'],
            'notes' => $validated['notes'],
            'old_stock' => $oldStock,
            'new_stock' => $product->stock,
        ]);

        return redirect()->back()->with('success', 'Stock adjusted successfully.');
    }

    /**
     * AJAX: Find an order by ID or Pathao consignment ID for sale returns.
     */
    public function findOrder(Request $request)
    {
        $query = trim($request->query('q'));
        if (!$query) {
            return response()->json(['success' => false, 'message' => 'Please enter an order or invoice number.']);
        }

        $order = \App\Models\Order::with('orderItems.product')
            ->where('id', $query)
            ->orWhere('pathao_consignment_id', $query)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => "No order found for \"{$query}\""]);
        }

        // Check if already processed as return
        $existingReturn = \App\Models\Transaction::where('reference_type', 'SaleReturn')
            ->where('reference_id', $order->id)
            ->exists();

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'address' => $order->address,
                'total_amount' => $order->total_amount,
                'delivery_charge' => $order->delivery_charge ?? 0,
                'paid_amount' => $order->paid_amount ?? 0,
                'status' => $order->status,
                'pathao_consignment_id' => $order->pathao_consignment_id,
                'created_at' => $order->created_at->format('d M Y, h:i A'),
                'items' => $order->orderItems->map(fn($item) => [
                    'product_name' => $item->product->name ?? 'Deleted Product',
                    'quantity' => $item->quantity,
                    'price' => $item->price_at_purchase,
                    'total' => $item->quantity * $item->price_at_purchase,
                ]),
                'already_returned' => $existingReturn,
            ]
        ]);
    }

    /**
     * Process a sale return: reverse the revenue, debit Pathao Clearing, restore stock.
     */
    public function saleReturn(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string|max:500',
            'restore_stock' => 'nullable|boolean',
        ]);

        $order = \App\Models\Order::with('orderItems.product')->findOrFail($validated['order_id']);

        // Prevent double-processing
        $existing = \App\Models\Transaction::where('reference_type', 'SaleReturn')
            ->where('reference_id', $order->id)->exists();
        if ($existing) {
            return redirect()->route('accounting.index', ['tab' => 'returns'])
                ->with('error', "Order #{$order->id} has already been processed as a sale return.");
        }

        DB::transaction(function () use ($order, $validated, $request) {
            // 1. Find and debit the Pathao Clearing account (receivable deduction)
            $clearingAccount = \App\Models\Account::where('name', 'Pathao Clearing')->first();

            if ($clearingAccount) {
                \App\Models\Transaction::create([
                    'account_id' => $clearingAccount->id,
                    'type' => 'out',
                    'amount' => $order->total_amount,
                    'reference_type' => 'SaleReturn',
                    'reference_id' => $order->id,
                    'date' => now(),
                    'notes' => "Sale Return: Order #{$order->id} ({$validated['reason']})" .
                               ($order->pathao_consignment_id ? " | Consignment: {$order->pathao_consignment_id}" : ''),
                ]);
                $clearingAccount->decrement('balance', $order->total_amount);
            }

            // 2. Reverse any existing "in" payment transactions for this order
            $existingPayments = \App\Models\Transaction::where('reference_type', 'Order')
                ->where('reference_id', $order->id)
                ->where('type', 'in')
                ->get();

            foreach ($existingPayments as $payment) {
                \App\Models\Transaction::create([
                    'account_id' => $payment->account_id,
                    'type' => 'out',
                    'amount' => $payment->amount,
                    'reference_type' => 'SaleReturn',
                    'reference_id' => $order->id,
                    'date' => now(),
                    'notes' => "Payment reversal for returned Order #{$order->id}",
                ]);
                $account = \App\Models\Account::find($payment->account_id);
                if ($account) {
                    $account->decrement('balance', $payment->amount);
                }
            }

            // 3. Restore stock if requested
            if ($request->boolean('restore_stock', true)) {
                foreach ($order->orderItems as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            }

            // 4. Update order status
            if (!in_array($order->status, ['failed', 'rejected', 'return_delivered'])) {
                $order->update(['status' => 'rejected']);
            }

            // 5. Log
            $order->logActivity('sale_return_processed', [
                'reason' => $validated['reason'],
                'amount_deducted' => $order->total_amount,
                'stock_restored' => $request->boolean('restore_stock', true),
                'reversed_payments' => $existingPayments->count(),
            ]);
        });

        return redirect()->route('accounting.index', ['tab' => 'returns'])
            ->with('success', "Sale return processed for Order #{$order->id}. Rs." . number_format($order->total_amount) . " deducted from Pathao receivables.");
    }

    public function exportReport(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $reportType = $request->query('report_type', 'pl');

        $filename = "{$reportType}_report_{$startDate}_to_{$endDate}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($request, $startDate, $endDate, $reportType) {
            $file = fopen('php://output', 'w');

            if ($reportType === 'pl') {
                fputcsv($file, ['Income Statement (P&L)', "From: $startDate", "To: $endDate"]);
                fputcsv($file, []);
                
                $deliveredOrders = \App\Models\Order::where('status', 'delivered')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->get();
                $revenue = $deliveredOrders->sum('total_amount');
                
                $cogs = 0;
                foreach ($deliveredOrders as $order) {
                    foreach ($order->orderItems as $item) {
                        $cogs += ($item->quantity * $item->cost_at_purchase);
                    }
                }
                
                $expenses = \App\Models\Expense::whereBetween('date', [$startDate, $endDate])->sum('amount');
                $gross = $revenue - $cogs;
                $net = $gross - $expenses;

                fputcsv($file, ['Description', 'Amount (Rs.)']);
                fputcsv($file, ['Total Revenue (Sales)', $revenue]);
                fputcsv($file, ['Less: Cost of Goods Sold', -$cogs]);
                fputcsv($file, ['Gross Profit', $gross]);
                fputcsv($file, ['Less: Operating Expenses', -$expenses]);
                fputcsv($file, ['Net Profit / (Loss)', $net]);

            } else {
                $partyId = $request->query('party_id');
                $accountId = $request->query('account_id');
                
                fputcsv($file, ['Transaction Ledger', "From: $startDate", "To: $endDate"]);
                fputcsv($file, []);
                fputcsv($file, ['Date', 'Account', 'Party', 'Notes', 'Debit (In)', 'Credit (Out)']);

                $query = \App\Models\Transaction::with(['account', 'party'])
                    ->whereBetween('date', [$startDate, $endDate]);
                
                if ($partyId) $query->where('party_id', $partyId);
                if ($accountId) $query->where('account_id', $accountId);

                $transactions = $query->orderBy('date', 'asc')->get();

                $totalIn = 0;
                $totalOut = 0;

                foreach ($transactions as $tx) {
                    if ($tx->type === 'in') $totalIn += $tx->amount;
                    else $totalOut += $tx->amount;

                    fputcsv($file, [
                        \Carbon\Carbon::parse($tx->date)->format('Y-m-d'),
                        $tx->account->name,
                        $tx->party ? $tx->party->name : '-',
                        $tx->notes ?: $tx->reference_type . ' #' . $tx->reference_id,
                        $tx->type === 'in' ? $tx->amount : '',
                        $tx->type === 'out' ? $tx->amount : ''
                    ]);
                }
                
                fputcsv($file, []);
                fputcsv($file, ['Totals', '', '', '', $totalIn, $totalOut]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
