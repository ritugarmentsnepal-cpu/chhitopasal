<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use \App\Traits\Loggable;

    protected $fillable = [
        'expense_category_id',
        'amount',
        'date',
        'description',
        'reference_no',
        'attachment_path'
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }
}
