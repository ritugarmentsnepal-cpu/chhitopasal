<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use \App\Traits\Loggable;

    protected $fillable = [
        'employee_id', 'month', 'year', 'base_salary', 'bonus', 'incentives', 
        'advance_deductions', 'absent_deductions', 'net_payable', 'status', 'payment_date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function deductedAdvances()
    {
        return $this->hasMany(EmployeeAdvance::class, 'deducted_in_payroll_id');
    }
}
