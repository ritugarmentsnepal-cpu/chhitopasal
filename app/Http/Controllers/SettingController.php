<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $tab = $request->query('tab', 'frontend');
        $allowedTabs = ['frontend', 'erp', 'integrations', 'automation', 'staff', 'danger'];
        if (!in_array($tab, $allowedTabs)) {
            $tab = 'frontend';
        }
        
        $data = [
            'settings' => $settings,
            'tab' => $tab,
        ];

        if ($tab === 'erp') {
            $data['accounts'] = \App\Models\Account::all();
        } elseif ($tab === 'staff') {
            $data['users'] = \App\Models\User::all();
        }

        return view('settings.index', $data);
    }

    public function store(Request $request)
    {
        $data = $request->except(['_token', 'hero_image', 'store_logo', 'store_favicon', 'redirect_tab']);
        $tab = $request->input('redirect_tab', 'frontend');

        // SEC-MED-02: Allowlist of valid setting keys to prevent arbitrary key injection
        $allowedKeys = [
            // Frontend
            'store_name', 'hero_title', 'hero_subtitle', 'hero_cta', 'meta_description',
            'contact_email', 'contact_phone', 'contact_address',
            'facebook_url', 'instagram_url', 'tiktok_url',
            'company_name', 'company_phone',
            // ERP
            'delivery_charge_inside', 'delivery_charge_outside',
            'default_cash_account', 'invoice_terms', 'invoice_footer',
            // Integrations (Pathao)
            'pathao_client_id', 'pathao_client_secret', 'pathao_username',
            'pathao_password', 'pathao_store_id',
            // Automation
            'auto_sync_pathao', 'pathao_sync_interval',
        ];

        // Handle image uploads separately
        $fileFields = ['hero_image', 'store_logo', 'store_favicon'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('settings', 'public');
                Setting::updateOrCreate(['key' => $field], ['value' => $path]);
            }
        }

        // Save string/boolean values — only allowed keys
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedKeys)) {
                continue; // SEC-MED-02: Skip any unknown keys
            }
            // SEC-PHASE-04: Auto-encrypt sensitive settings before saving
            $value = encrypt_setting_value($key, $value ?? '');
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Clear cached settings so changes take effect immediately
        clear_settings_cache();

        return redirect()->route('settings.index', ['tab' => $tab])->with('success', 'Settings updated successfully.');
    }
    
    public function testPathao(Request $request)
    {
        try {
            $pathao = new \App\Services\PathaoService();
            // Just fetching cities is enough to verify token works
            $cities = $pathao->getCities();
            
            if (empty($cities)) {
                return redirect()->route('settings.index', ['tab' => 'integrations'])
                    ->with('error', 'Connection test failed. Please check your credentials.');
            }

            return redirect()->route('settings.index', ['tab' => 'integrations'])
                ->with('success', 'Connection successful! API is working perfectly.');
                
        } catch (\Exception $e) {
            return redirect()->route('settings.index', ['tab' => 'integrations'])
                ->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    public function factoryReset(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation_word' => 'required|string|in:RESET',
        ]);

        // Verify admin password
        if (!\Illuminate\Support\Facades\Hash::check($request->password, auth()->user()->password)) {
            return redirect()->route('settings.index', ['tab' => 'danger'])
                ->with('error', 'Incorrect password. Factory reset aborted.');
        }

        // Preserve current admin user
        $adminUser = auth()->user();

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Truncate all data tables (order matters due to relationships)
        \App\Models\ActivityLog::truncate();
        \App\Models\Transaction::truncate();
        \App\Models\OrderItem::truncate();
        \App\Models\Order::truncate();
        \App\Models\PurchaseItem::truncate();
        \App\Models\Purchase::truncate();
        \App\Models\Expense::truncate();
        \App\Models\ExpenseCategory::truncate();
        \App\Models\Product::truncate();
        \App\Models\Category::truncate();
        \App\Models\Account::truncate();
        \App\Models\Party::truncate();
        \App\Models\Setting::truncate();

        // Delete all users except current admin
        \App\Models\User::where('id', '!=', $adminUser->id)->delete();

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Re-seed essential system accounts that the app depends on
        \App\Models\Account::create(['name' => 'Main Cash', 'type' => 'cash', 'balance' => 0]);
        \App\Models\Account::create(['name' => 'Pathao Clearing', 'type' => 'clearing', 'balance' => 0]);
        \App\Models\Party::create(['name' => 'Pathao Parcel', 'type' => 'pathao', 'current_balance' => 0]);

        // Clean uploaded storage
        Storage::disk('public')->deleteDirectory('products');
        Storage::disk('public')->deleteDirectory('settings');
        Storage::disk('public')->deleteDirectory('expenses');
        Storage::disk('public')->deleteDirectory('purchases');

        // Clear all caches
        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->route('settings.index', ['tab' => 'danger'])
            ->with('success', 'Factory reset complete. All data has been wiped. You can start fresh.');
    }
}
