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
            'amount' => 'required|numeric|min:1',
            'account_id' => 'required|exists:accounts,id',
            'date' => 'required|date',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $pathaoParty = Party::where('type', 'pathao')->firstOrFail();
        $account = Account::findOrFail($request->account_id);

        // Record the money coming into our Bank/Cash
        Transaction::create([
            'account_id' => $account->id,
            'party_id' => $pathaoParty->id,
            'type' => 'in',
            'amount' => $request->amount,
            'reference_type' => \App\SystemAccounts::REF_PATHAO_SETTLEMENT,
            'date' => $request->date,
            'notes' => $request->notes ?: 'Bulk COD Settlement from Pathao'
        ]);

        // Increment our bank balance
        $account->increment('balance', $request->amount);

        // Decrement Pathao's debt to us
        $pathaoParty->decrement('current_balance', $request->amount);

        return back()->with('success', "Successfully recorded settlement of Rs. {$request->amount} from Pathao.");
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
