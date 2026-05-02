<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['name', 'type', 'balance', 'account_number', 'bank_name', 'branch'];

    /**
     * System account names that cannot be deleted or renamed.
     */
    public const PROTECTED_NAMES = ['Main Cash', 'Pathao Clearing'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if this is a system-protected account.
     */
    public function isProtected(): bool
    {
        return in_array($this->name, self::PROTECTED_NAMES);
    }

    /**
     * Get display icon class based on type.
     */
    public function getTypeIcon(): string
    {
        return match($this->type) {
            'bank' => '🏦',
            'cash' => '💵',
            'mobile_wallet' => '📱',
            'clearing' => '🔄',
            default => '💰',
        };
    }
}
