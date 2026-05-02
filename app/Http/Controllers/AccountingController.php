<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    /**
     * AUTH-01: Helper to check if the current user has financial access.
     */
    private function requireFinancialAccess()
    {
        if (in_array(auth()->user()->role, ['operational_staff'])) {
            abort(403, 'Access Denied: You do not have permission to perform this action.');
        }
    }

    public function index(Request $request)
    {
        $this->requireFinancialAccess();

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
            $data['parties'] = \App\Models\Party::all();
        } elseif ($tab === 'invoices') {
            $data['orders'] = \App\Models\Order::with('orderItems')->latest()->paginate(20);
        } elseif ($tab === 'returns') {
            // PERF-02: Paginate returns instead of loading all
            $data['returned_orders'] = \App\Models\Order::with('orderItems')
                ->whereIn('status', ['failed', 'rejected', 'return_delivered'])
                ->latest()->paginate(20);
            $data['pathao_clearing'] = \App\Models\Account::where('name', 'Pathao Clearing')->first();
            $data['accounts'] = \App\Models\Account::all();
        } elseif ($tab === 'parties') {
            $data['parties'] = \App\Models\Party::all();
            // NEW-FIN-01: Compute payables (negative balance = owed to supplier) and receivables
            $data['payables'] = \App\Models\Party::where('current_balance', '<', 0)->sum(\Illuminate\Support\Facades\DB::raw('ABS(current_balance)'));
            $data['receivables'] = \App\Models\Party::where('current_balance', '>', 0)->sum('current_balance');
        } elseif ($tab === 'purchases') {
            // PERF-02: Paginate purchases
            $data['purchases'] = \App\Models\Purchase::with('party')->latest()->paginate(20);
            $data['parties'] = \App\Models\Party::whereIn('type', ['supplier'])->get();
        } elseif ($tab === 'expenses') {
            $data['expenses'] = \App\Models\Expense::with('category')->latest()->paginate(20);
            $data['categories'] = \App\Models\ExpenseCategory::all();
            $data['accounts'] = \App\Models\Account::all();
        } elseif ($tab === 'banking') {
            $data['accounts'] = \App\Models\Account::all();
            $data['transactions'] = \App\Models\Transaction::with(['account', 'party'])->latest()->paginate(20);
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
                // PERF-03: Use DB aggregates instead of loading all orders into PHP memory
                $data['pl_revenue'] = \App\Models\Order::where('status', 'delivered')
                    ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->sum('total_amount');
                
                $data['pl_cogs'] = DB::table('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.status', 'delivered')
                    ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->sum(DB::raw('order_items.quantity * order_items.cost_at_purchase'));

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

        // FIN-CRIT-03: Mark delivered Pathao orders as paid AND record financial transactions.
        // OrderService::recordDeliveryRevenue() already creates the Pathao Clearing receivable
        // when status transitions to 'delivered'. This sync confirms the payment is received.
        $orders = \App\Models\Order::where('status', 'delivered')
                    ->where('payment_status', 'unpaid')
                    ->whereNotNull('pathao_consignment_id')
                    ->get();

        $syncedCount = 0;

        DB::transaction(function () use ($orders, &$syncedCount) {
            foreach ($orders as $order) {
                // Check if a delivery revenue transaction already exists
                $hasRevenueTx = \App\Models\Transaction::where('reference_type', \App\SystemAccounts::REF_ORDER_DELIVERED)
                    ->where('reference_id', $order->id)
                    ->exists();

                // If no revenue transaction exists yet, record one now
                if (!$hasRevenueTx) {
                    $clearingAccount = \App\SystemAccounts::pathaoClearingAccount();
                    $pathaoParty = \App\SystemAccounts::pathaoParty();

                    if ($clearingAccount && $pathaoParty) {
                        $dueAmount = $order->total_amount - ($order->paid_amount ?? 0);
                        if ($dueAmount > 0) {
                            \App\Models\Transaction::create([
                                'account_id' => $clearingAccount->id,
                                'party_id' => $pathaoParty->id,
                                'type' => 'in',
                                'amount' => $dueAmount,
                                'reference_type' => \App\SystemAccounts::REF_ORDER_DELIVERED,
                                'reference_id' => $order->id,
                                'date' => now(),
                                'notes' => "Receivable from Pathao for Order #{$order->id} (sync)",
                            ]);
                            $clearingAccount->increment('balance', $dueAmount);
                            $pathaoParty->increment('current_balance', $dueAmount);
                        }
                    }
                }

                $order->update([
                    'payment_status' => 'paid',
                    'paid_amount' => $order->total_amount,
                ]);
                $syncedCount++;
            }
        });

        return redirect()->route('accounting.index', ['tab' => 'banking'])->with('success', "Synced {$syncedCount} Pathao settlements successfully.");
    }

    public function payPurchase(Request $request)
    {
        $this->requireFinancialAccess();
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
                'reference_type' => \App\SystemAccounts::REF_PURCHASE,
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
        $this->requireFinancialAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        \App\Models\ExpenseCategory::create($validated);

        return redirect()->route('accounting.index', ['tab' => 'expenses'])->with('success', 'Expense Category added successfully.');
    }

    public function storeParty(Request $request)
    {
        $this->requireFinancialAccess();

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

    public function updateParty(Request $request, \App\Models\Party $party)
    {
        $this->requireFinancialAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:customer,supplier,pathao',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'opening_balance' => 'nullable|numeric',
            'current_balance' => 'nullable|numeric',
        ]);

        $party->update($validated);

        return redirect()->route('accounting.index', ['tab' => 'parties'])->with('success', 'Party updated successfully.');
    }

    public function storeTransaction(Request $request)
    {
        $this->requireFinancialAccess();

        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'party_id' => 'nullable|exists:parties,id',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $account = \App\Models\Account::findOrFail($validated['account_id']);

                // FIN-02: Guard against negative balance on outgoing transactions
                if ($validated['type'] === 'out' && $account->balance < $validated['amount']) {
                    throw new \RuntimeException("Insufficient balance in {$account->name}. Available: Rs." . number_format($account->balance, 2) . ", Requested: Rs." . number_format($validated['amount'], 2));
                }

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
        } catch (\RuntimeException $e) {
            return redirect()->route('accounting.index', ['tab' => 'banking'])->with('error', $e->getMessage());
        }

        return redirect()->route('accounting.index', ['tab' => 'banking'])->with('success', 'Manual transaction recorded successfully.');
    }

    public function adjustStock(Request $request)
    {
        $this->requireFinancialAccess();

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
        $this->requireFinancialAccess();
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
            // FIN-02: Reverse both 'Order' and 'Order Delivered' reference types
            $existingPayments = \App\Models\Transaction::where(function($q) use ($order) {
                    $q->where(function($q2) use ($order) {
                        $q2->where('reference_type', \App\SystemAccounts::REF_ORDER)
                           ->where('reference_id', $order->id);
                    })->orWhere(function($q2) use ($order) {
                        $q2->where('reference_type', \App\SystemAccounts::REF_ORDER_DELIVERED)
                           ->where('reference_id', $order->id);
                    });
                })
                ->where('type', 'in')
                ->get();

            foreach ($existingPayments as $payment) {
                \App\Models\Transaction::create([
                    'account_id' => $payment->account_id,
                    'type' => 'out',
                    'amount' => $payment->amount,
                    'reference_type' => \App\SystemAccounts::REF_SALE_RETURN,
                    'reference_id' => $order->id,
                    'date' => now(),
                    'notes' => "Payment reversal for returned Order #{$order->id}",
                ]);
                $account = \App\Models\Account::find($payment->account_id);
                if ($account) {
                    // FIN-MED-02: Guard and log if balance would go negative
                    if ($account->balance < $payment->amount) {
                        \Illuminate\Support\Facades\Log::warning("Account '{$account->name}' balance going negative during sale return", [
                            'account_id' => $account->id,
                            'current_balance' => $account->balance,
                            'deduction' => $payment->amount,
                            'order_id' => $order->id,
                        ]);
                    }
                    $account->decrement('balance', $payment->amount);
                }
                // Also reverse the Pathao party balance if applicable
                if ($payment->party_id) {
                    $party = \App\Models\Party::find($payment->party_id);
                    if ($party) {
                        $party->decrement('current_balance', $payment->amount);
                    }
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
                
                // BACK-03: Use DB aggregates instead of loading all orders into memory
                $revenue = \App\Models\Order::where('status', 'delivered')
                    ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->sum('total_amount');
                
                $cogs = DB::table('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.status', 'delivered')
                    ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->sum(DB::raw('order_items.quantity * order_items.cost_at_purchase'));
                
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

                $totalIn = 0;
                $totalOut = 0;

                // BACK-03: Use chunk to prevent memory exhaustion on large datasets
                $query->orderBy('date', 'asc')->chunk(200, function ($transactions) use ($file, &$totalIn, &$totalOut) {
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
                });
                
                fputcsv($file, []);
                fputcsv($file, ['Totals', '', '', '', $totalIn, $totalOut]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Create a new account (bank, cash, wallet).
     */
    public function storeAccount(Request $request)
    {
        $this->requireFinancialAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:accounts,name',
            'type' => 'required|in:cash,bank,mobile_wallet,clearing',
            'account_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $account = \App\Models\Account::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'account_number' => $validated['account_number'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'branch' => $validated['branch'] ?? null,
                'balance' => $validated['opening_balance'] ?? 0,
            ]);

            // Record opening balance transaction if > 0
            if (($validated['opening_balance'] ?? 0) > 0) {
                \App\Models\Transaction::create([
                    'account_id' => $account->id,
                    'type' => 'in',
                    'amount' => $validated['opening_balance'],
                    'reference_type' => 'Opening Balance',
                    'date' => now(),
                    'notes' => 'Opening balance for ' . $account->name,
                ]);
            }
        });

        return redirect()->route('accounting.index', ['tab' => 'banking'])->with('success', "Account \"{$validated['name']}\" created successfully.");
    }

    /**
     * Update an existing account.
     */
    public function updateAccount(Request $request, \App\Models\Account $account)
    {
        $this->requireFinancialAccess();

        // Protect system accounts from rename
        if ($account->isProtected() && $request->name !== $account->name) {
            return redirect()->route('accounting.index', ['tab' => 'banking'])
                ->with('error', "\"{$account->name}\" is a system account and cannot be renamed.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:accounts,name,' . $account->id,
            'type' => 'required|in:cash,bank,mobile_wallet,clearing',
            'account_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
        ]);

        $account->update($validated);

        return redirect()->route('accounting.index', ['tab' => 'banking'])->with('success', "Account \"{$account->name}\" updated.");
    }

    /**
     * Delete an account (only if zero balance + no transactions).
     */
    public function destroyAccount(\App\Models\Account $account)
    {
        $this->requireFinancialAccess();

        if ($account->isProtected()) {
            return redirect()->route('accounting.index', ['tab' => 'banking'])
                ->with('error', "\"{$account->name}\" is a system account and cannot be deleted.");
        }

        if ($account->balance != 0) {
            return redirect()->route('accounting.index', ['tab' => 'banking'])
                ->with('error', "Cannot delete \"{$account->name}\" — balance is not zero (Rs. " . number_format($account->balance, 2) . ").");
        }

        if ($account->transactions()->count() > 0) {
            return redirect()->route('accounting.index', ['tab' => 'banking'])
                ->with('error', "Cannot delete \"{$account->name}\" — it has existing transactions.");
        }

        $name = $account->name;
        $account->delete();

        return redirect()->route('accounting.index', ['tab' => 'banking'])->with('success', "Account \"{$name}\" deleted.");
    }

    /**
     * Transfer funds between two accounts.
     */
    public function transferFunds(Request $request)
    {
        $this->requireFinancialAccess();

        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $from = \App\Models\Account::findOrFail($validated['from_account_id']);
                $to = \App\Models\Account::findOrFail($validated['to_account_id']);

                if ($from->balance < $validated['amount']) {
                    throw new \RuntimeException("Insufficient balance in {$from->name}. Available: Rs." . number_format($from->balance, 2));
                }

                $transferNote = $validated['notes'] ?: "Transfer: {$from->name} → {$to->name}";

                // Out from source
                \App\Models\Transaction::create([
                    'account_id' => $from->id,
                    'type' => 'out',
                    'amount' => $validated['amount'],
                    'reference_type' => 'Transfer',
                    'reference_id' => $to->id,
                    'date' => now(),
                    'notes' => $transferNote,
                ]);
                $from->decrement('balance', $validated['amount']);

                // In to destination
                \App\Models\Transaction::create([
                    'account_id' => $to->id,
                    'type' => 'in',
                    'amount' => $validated['amount'],
                    'reference_type' => 'Transfer',
                    'reference_id' => $from->id,
                    'date' => now(),
                    'notes' => $transferNote,
                ]);
                $to->increment('balance', $validated['amount']);
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('accounting.index', ['tab' => 'banking'])->with('error', $e->getMessage());
        }

        return redirect()->route('accounting.index', ['tab' => 'banking'])->with('success', 'Transfer completed successfully.');
    }

    /**
     * Show per-account statement with running balance.
     */
    public function accountStatement(Request $request, \App\Models\Account $account)
    {
        $this->requireFinancialAccess();

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        // Opening balance = account balance at start of period
        $openingBalance = $account->balance;

        // Get all transactions AFTER end date to calculate opening
        $afterEndDate = \App\Models\Transaction::where('account_id', $account->id)
            ->where('date', '>', $endDate . ' 23:59:59')
            ->get();
        
        // Get all transactions in period
        $transactions = \App\Models\Transaction::with('party')
            ->where('account_id', $account->id)
            ->whereBetween('date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Calculate opening balance by reverse-engineering from current balance
        $currentBalance = $account->balance;
        
        // Reverse transactions after end date
        foreach ($afterEndDate as $tx) {
            if ($tx->type === 'in') {
                $currentBalance -= $tx->amount;
            } else {
                $currentBalance += $tx->amount;
            }
        }
        $closingBalance = $currentBalance;

        // Reverse period transactions to get opening
        foreach ($transactions->reverse() as $tx) {
            if ($tx->type === 'in') {
                $currentBalance -= $tx->amount;
            } else {
                $currentBalance += $tx->amount;
            }
        }
        $openingBalance = $currentBalance;

        // Calculate running balance for each transaction
        $runningBalance = $openingBalance;
        $transactionsWithBalance = [];
        foreach ($transactions as $tx) {
            if ($tx->type === 'in') {
                $runningBalance += $tx->amount;
            } else {
                $runningBalance -= $tx->amount;
            }
            $tx->running_balance = $runningBalance;
            $transactionsWithBalance[] = $tx;
        }

        // Period totals
        $totalIn = $transactions->where('type', 'in')->sum('amount');
        $totalOut = $transactions->where('type', 'out')->sum('amount');

        return view('accounting.statement', compact(
            'account', 'transactionsWithBalance', 'startDate', 'endDate',
            'openingBalance', 'closingBalance', 'totalIn', 'totalOut'
        ));
    }

    /**
     * Export per-account statement as CSV.
     */
    public function exportStatement(Request $request, \App\Models\Account $account)
    {
        $this->requireFinancialAccess();

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $filename = str_replace(' ', '_', strtolower($account->name)) . "_statement_{$startDate}_to_{$endDate}.csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
        ];

        $callback = function() use ($account, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Account Statement: ' . $account->name, 'Type: ' . ucfirst($account->type)]);
            fputcsv($file, ['Period: ' . $startDate . ' to ' . $endDate]);
            if ($account->bank_name) fputcsv($file, ['Bank: ' . $account->bank_name, 'Branch: ' . ($account->branch ?? '-'), 'A/C: ' . ($account->account_number ?? '-')]);
            fputcsv($file, []);

            $transactions = \App\Models\Transaction::with('party')
                ->where('account_id', $account->id)
                ->whereBetween('date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            // Calculate opening balance
            $afterEndDate = \App\Models\Transaction::where('account_id', $account->id)
                ->where('date', '>', $endDate . ' 23:59:59')->get();
            $bal = $account->balance;
            foreach ($afterEndDate as $tx) {
                $bal = $tx->type === 'in' ? $bal - $tx->amount : $bal + $tx->amount;
            }
            foreach ($transactions->reverse() as $tx) {
                $bal = $tx->type === 'in' ? $bal - $tx->amount : $bal + $tx->amount;
            }
            $openingBalance = $bal;

            fputcsv($file, ['Date', 'Reference', 'Party', 'Notes', 'Debit (In)', 'Credit (Out)', 'Balance']);
            fputcsv($file, ['', '', '', 'Opening Balance', '', '', $openingBalance]);

            $running = $openingBalance;
            foreach ($transactions as $tx) {
                $running = $tx->type === 'in' ? $running + $tx->amount : $running - $tx->amount;
                fputcsv($file, [
                    \Carbon\Carbon::parse($tx->date)->format('Y-m-d'),
                    $tx->reference_type ? $tx->reference_type . ' #' . $tx->reference_id : 'Manual',
                    $tx->party ? $tx->party->name : '-',
                    $tx->notes ?? '-',
                    $tx->type === 'in' ? $tx->amount : '',
                    $tx->type === 'out' ? $tx->amount : '',
                    $running,
                ]);
            }

            fputcsv($file, ['', '', '', 'Closing Balance', '', '', $running]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * FIN-MED-03: Reconciliation check — compare account.balance vs SUM(transactions).
     * Detects silent ledger drift.
     */
    public function reconcile()
    {
        $this->requireFinancialAccess();

        $accounts = \App\Models\Account::all();
        $discrepancies = [];

        foreach ($accounts as $account) {
            $txIn = \App\Models\Transaction::where('account_id', $account->id)
                ->where('type', 'in')->sum('amount');
            $txOut = \App\Models\Transaction::where('account_id', $account->id)
                ->where('type', 'out')->sum('amount');
            $computedBalance = $txIn - $txOut;

            if (abs($account->balance - $computedBalance) > 0.01) {
                $discrepancies[] = [
                    'account' => $account->name,
                    'stored_balance' => $account->balance,
                    'computed_balance' => $computedBalance,
                    'drift' => $account->balance - $computedBalance,
                ];
            }
        }

        if (empty($discrepancies)) {
            return back()->with('success', '✅ All accounts reconciled — no discrepancies found.');
        }

        $msg = '⚠️ Discrepancies found: ';
        foreach ($discrepancies as $d) {
            $msg .= "{$d['account']}: stored Rs." . number_format($d['stored_balance'], 2) .
                    " vs computed Rs." . number_format($d['computed_balance'], 2) .
                    " (drift: Rs." . number_format($d['drift'], 2) . "). ";
        }

        \Illuminate\Support\Facades\Log::warning('Account reconciliation discrepancies', $discrepancies);

        return back()->with('error', $msg);
    }
}
