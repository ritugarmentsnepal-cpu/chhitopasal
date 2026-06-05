<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\Party;
use App\Models\Account;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PathaoReconciliationController extends Controller
{
    public function index()
    {
        // View to upload CSV
        return view('pathao.reconcile.index');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'statement_file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        $file = $request->file('statement_file');
        
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } catch (\Exception $e) {
            return back()->with('error', 'Error reading file: ' . $e->getMessage());
        }

        if (count($rows) < 2) {
            return back()->with('error', 'The uploaded file appears to be empty.');
        }

        // Map Headers to guess the columns
        $headers = array_map('strtolower', array_map('trim', $rows[0]));
        
        $consignmentCol = -1;
        $collectedCol = -1;
        $deliveryFeeCol = -1;
        $merchantOrderCol = -1;

        foreach ($headers as $index => $header) {
            if (str_contains($header, 'consignment')) $consignmentCol = $index;
            elseif (str_contains($header, 'merchant order') || str_contains($header, 'order id')) $merchantOrderCol = $index;
            elseif (str_contains($header, 'collected') || str_contains($header, 'cod')) $collectedCol = $index;
            elseif (str_contains($header, 'delivery charge') || str_contains($header, 'delivery fee')) $deliveryFeeCol = $index;
        }

        if ($consignmentCol === -1 || $collectedCol === -1 || $deliveryFeeCol === -1) {
            return back()->with('error', 'Could not find required columns. Please ensure your file has "Consignment ID", "Collected Amount", and "Delivery Charge" columns.');
        }

        $previewData = [];
        $totalOrdersFound = 0;
        $totalDiscrepancies = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Skip empty rows
            if (!isset($row[$consignmentCol]) || trim($row[$consignmentCol]) === '') {
                continue;
            }

            $consignmentId = trim($row[$consignmentCol]);
            $merchantOrderId = $merchantOrderCol !== -1 && isset($row[$merchantOrderCol]) ? trim($row[$merchantOrderCol]) : null;
            $collected = (float) str_replace(',', '', $row[$collectedCol] ?? 0);
            $deliveryFee = (float) str_replace(',', '', $row[$deliveryFeeCol] ?? 0);

            // Find Order
            $order = Order::where('pathao_consignment_id', $consignmentId)
                ->orWhere(function($q) use ($merchantOrderId) {
                    if ($merchantOrderId) {
                        $q->where('order_id', $merchantOrderId);
                    }
                })->first();

            $status = 'red'; // Default to not found
            $reason = [];
            
            if (!$order) {
                $reason[] = 'Order not found in database';
            } else {
                $totalOrdersFound++;
                if ($order->pathao_settled_at) {
                    $status = 'yellow';
                    $reason[] = 'Already Settled previously';
                } else {
                    $status = 'green';
                }

                // Discrepancy checks
                if ($order->status !== 'delivered' && $order->status !== 'return_delivered') {
                    $status = 'yellow';
                    $reason[] = "System status is {$order->status}";
                }

                // If delivered, collected should match total_amount
                if ($order->status === 'delivered') {
                    // Buffer of 10 rupees allowed
                    if (abs($collected - $order->total_amount) > 10) {
                        $status = 'red';
                        $reason[] = "COD mismatch (Expected: {$order->total_amount}, Pathao: {$collected})";
                    }
                }
            }

            if ($status !== 'green') {
                $totalDiscrepancies++;
            }

            $previewData[] = [
                'row_index' => $i,
                'consignment_id' => $consignmentId,
                'merchant_order_id' => $merchantOrderId,
                'collected' => $collected,
                'delivery_fee' => $deliveryFee,
                'order' => $order,
                'status_color' => $status,
                'reasons' => implode(' | ', $reason)
            ];
        }

        // Cache the processed data for the settle step
        cache()->put('pathao_reconciliation_' . auth()->id(), $previewData, now()->addHours(1));

        return view('pathao.reconcile.preview', compact('previewData', 'totalOrdersFound', 'totalDiscrepancies'));
    }

    public function process(Request $request)
    {
        $previewData = cache()->get('pathao_reconciliation_' . auth()->id());
        
        if (!$previewData) {
            return redirect()->route('pathao.reconcile.index')->with('error', 'Reconciliation session expired. Please upload the file again.');
        }

        $actions = $request->input('actions', []); // array of row_index => 'settle' or 'dispute'
        $accountId = $request->input('account_id');
        $settlementDate = $request->input('date', now()->toDateString());

        $account = Account::findOrFail($accountId);
        $pathaoParty = Party::where('type', 'pathao')->firstOrFail();
        $clearingAccount = \App\SystemAccounts::pathaoClearingAccount();

        $totalCollected = 0;
        $totalDeliveryFee = 0;
        $settledCount = 0;
        $disputeCount = 0;

        DB::beginTransaction();
        try {
            foreach ($previewData as $data) {
                $rowIndex = $data['row_index'];
                $action = $actions[$rowIndex] ?? 'ignore';

                if ($action === 'ignore') {
                    continue;
                }

                $order = Order::find($data['order'] ? $data['order']->id : null);
                
                if (!$order) {
                    continue; // Cannot process if order isn't linked
                }

                if ($action === 'dispute') {
                    $order->update(['pathao_disputed' => true]);
                    $disputeCount++;
                    continue;
                }

                if ($action === 'settle') {
                    // Update Order
                    $order->update([
                        'pathao_settled_at' => now(),
                        'pathao_disputed' => false,
                        'pathao_actual_delivery_charge' => $data['delivery_fee']
                    ]);

                    $totalCollected += $data['collected'];
                    $totalDeliveryFee += $data['delivery_fee'];
                    $settledCount++;
                }
            }

            // Generate Ledger Entries if anything was settled
            if ($settledCount > 0) {
                // Net settlement amount
                $netAmount = $totalCollected - $totalDeliveryFee;

                if ($netAmount > 0) {
                    Transaction::create([
                        'account_id' => $account->id,
                        'type' => 'in',
                        'amount' => $netAmount,
                        'reference_type' => \App\SystemAccounts::REF_PATHAO_SETTLEMENT,
                        'date' => $settlementDate,
                        'notes' => "Automated Pathao Reconciliation ({$settledCount} orders)"
                    ]);
                    $account->increment('balance', $netAmount);
                }

                if ($totalDeliveryFee > 0) {
                    $cat = \App\Models\ExpenseCategory::firstOrCreate(['name' => 'Logistics & Shipping']);
                    \App\Models\Expense::create([
                        'expense_category_id' => $cat->id,
                        'amount' => $totalDeliveryFee,
                        'date' => $settlementDate,
                        'description' => "Pathao Delivery Fees (Auto-Reconciled {$settledCount} orders)"
                    ]);
                }

                if ($clearingAccount) {
                    Transaction::create([
                        'account_id' => $clearingAccount->id,
                        'party_id' => $pathaoParty->id,
                        'type' => 'out',
                        'amount' => $totalCollected, // We clear the total debt
                        'reference_type' => \App\SystemAccounts::REF_PATHAO_SETTLEMENT,
                        'date' => $settlementDate,
                        'notes' => 'Settlement reconciliation clearing'
                    ]);
                    $clearingAccount->decrement('balance', $totalCollected);
                }

                $pathaoParty->decrement('current_balance', $totalCollected);
            }

            DB::commit();
            cache()->forget('pathao_reconciliation_' . auth()->id());

            return redirect()->route('pathao.index')->with('success', "Reconciliation Complete! Settled {$settledCount} orders. Disputed {$disputeCount} orders. Net bank deposit: Rs. {$netAmount}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pathao Reconcile Error: ' . $e->getMessage());
            return back()->with('error', 'Error processing reconciliation: ' . $e->getMessage());
        }
    }
}
