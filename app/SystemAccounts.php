<?php

namespace App;

/**
 * System-wide constants for account names and party types
 * that the application logic depends on.
 *
 * ARCH-04: Centralizes hardcoded account/party names to prevent
 * silent breakage if someone renames them in the UI.
 */
class SystemAccounts
{
    public const MAIN_CASH = 'Main Cash';
    public const PATHAO_CLEARING = 'Pathao Clearing';
    public const PATHAO_PARTY_TYPE = 'pathao';

    // Transaction reference types
    public const REF_ORDER = 'Order';
    public const REF_ORDER_DELIVERED = 'Order Delivered';
    public const REF_SALE_RETURN = 'SaleReturn';
    public const REF_PURCHASE = 'Purchase';
    public const REF_EXPENSE = 'Expense';
    public const REF_PATHAO_SETTLEMENT = 'Pathao Settlement';

    /**
     * Get the Main Cash account, or null if not found.
     */
    public static function mainCash(): ?\App\Models\Account
    {
        return \App\Models\Account::where('name', self::MAIN_CASH)->first();
    }

    /**
     * Get the Pathao Clearing account, or null if not found.
     */
    public static function pathaoClearingAccount(): ?\App\Models\Account
    {
        return \App\Models\Account::where('name', self::PATHAO_CLEARING)->first();
    }

    /**
     * Get the Pathao party record, or null if not found.
     */
    public static function pathaoParty(): ?\App\Models\Party
    {
        return \App\Models\Party::where('type', self::PATHAO_PARTY_TYPE)->first();
    }
}
