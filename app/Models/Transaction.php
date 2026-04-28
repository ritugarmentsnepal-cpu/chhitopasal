<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use \App\Traits\Loggable;

    protected $fillable = [
        'account_id', 'type', 'amount', 'reference_type', 'reference_id', 'party_id', 'date', 'notes', 'attachment_path'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }
}
