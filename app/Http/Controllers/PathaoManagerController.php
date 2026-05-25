<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Party;
use App\Models\Transaction;
use App\Models\Account;
use App\Services\PathaoService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PathaoManagerController extends Controller
{
    public function index(Request $request, PathaoService $pathao)
    {
        // 1. Dashboard Metrics
        $inTransitCount = Order::where('status', 'shipped')->count();
        $deliveredCount = Order::where('status', 'delivered')->count();
        
        $pathaoParty = Party::firstOrCreate(
            ['type' => 'pathao'],
            ['name' => 'Pathao Parcel', 'current_balance' => 0]
        );

        $pendingFromPathao = $pathaoParty->current_balance;

        // 2. Deliveries (Shipped and Delivered orders)
        $deliveries = Order::whereIn('status', ['shipped', 'delivered', 'return_delivered'])
            ->whereNotNull('pathao_consignment_id')
            ->latest()
            ->paginate(20);

        // 3. Accounts for settlement
        $accounts = Account::all();

        // 4. Financial Statements (Ledger of Pathao Party)
        $ledger = Transaction::where('party_id', $pathaoParty->id)
            ->latest('date')
            ->latest('id')
            ->paginate(50, ['*'], 'ledger_page');

        return view('pathao.index', compact(
            'inTransitCount', 
            'deliveredCount', 
            'pendingFromPathao', 
            'deliveries', 
            'ledger',
            'accounts'
        ));
    }

    public function recordSettlement(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'delivery_charge' => 'nullable|numeric|min:0',
            'account_id' => 'required|exists:accounts,id',
            'date' => 'required|date',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string|max:2000'
        ]);

        $pathaoParty = Party::where('type', 'pathao')->firstOrFail();
        $account = Account::findOrFail($request->account_id);
        $clearingAccount = \App\SystemAccounts::pathaoClearingAccount();

        $amount = (float) $request->amount;
        $deliveryCharge = (float) ($request->delivery_charge ?: 0);
        $totalSettled = $amount + $deliveryCharge;

        if ($totalSettled <= 0) {
            return back()->with('error', 'Total settled amount must be greater than zero.');
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($amount, $deliveryCharge, $totalSettled, $account, $pathaoParty, $clearingAccount, $request) {
            // 1. Record the money coming into our Bank/Cash (party_id is null so it doesn't double-count in party ledger)
            if ($amount > 0) {
                Transaction::create([
                    'account_id' => $account->id,
                    'party_id' => null,
                    'type' => 'in',
                    'amount' => $amount,
                    'reference_type' => \App\SystemAccounts::REF_PATHAO_SETTLEMENT,
                    'date' => $request->date,
                    'notes' => $request->notes ?: 'Bulk COD Settlement from Pathao'
                ]);
                $account->increment('balance', $amount);
            }

            // 2. Record the Delivery Expense
            if ($deliveryCharge > 0) {
                $cat = \App\Models\ExpenseCategory::firstOrCreate(['name' => 'Logistics & Shipping']);
                \App\Models\Expense::create([
                    'expense_category_id' => $cat->id,
                    'amount' => $deliveryCharge,
                    'date' => $request->date,
                    'description' => 'Pathao Delivery Charges deducted from settlement'
                ]);
            }

            // 3. Reconcile Pathao's Debt (Clearing Account & Party Ledger)
            if ($clearingAccount) {
                Transaction::create([
                    'account_id' => $clearingAccount->id,
                    'party_id' => $pathaoParty->id, // This links the credit to the Party Ledger
                    'type' => 'out',
                    'amount' => $totalSettled,
                    'reference_type' => \App\SystemAccounts::REF_PATHAO_SETTLEMENT,
                    'date' => $request->date,
                    'notes' => 'Settlement reconciliation (clearing)'
                ]);
                $clearingAccount->decrement('balance', $totalSettled);
            }

            $pathaoParty->decrement('current_balance', $totalSettled);
        });

        return back()->with('success', "Successfully recorded settlement. Bank received Rs. {$amount}, fees deducted Rs. {$deliveryCharge}.");
    }

    public function getCities(PathaoService $pathao)
    {
        return response()->json($pathao->getCities());
    }

    public function getZones($cityId, PathaoService $pathao)
    {
        // SEC-HIGH-05: Validate parameter to prevent injection
        $cityId = (int) $cityId;
        if ($cityId <= 0) return response()->json([], 400);
        return response()->json($pathao->getZones($cityId));
    }

    public function getAreas($zoneId, PathaoService $pathao)
    {
        // SEC-HIGH-05: Validate parameter to prevent injection
        $zoneId = (int) $zoneId;
        if ($zoneId <= 0) return response()->json([], 400);
        return response()->json($pathao->getAreas($zoneId));
    }
}
