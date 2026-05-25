<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAdvance extends Model
{
    use \App\Traits\Loggable;

    protected $fillable = [
        'employee_id', 'amount', 'date', 'description', 'deducted_in_payroll_id'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'deducted_in_payroll_id');
    }
}
