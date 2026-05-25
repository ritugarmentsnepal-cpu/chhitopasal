<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use \App\Traits\Loggable;

    protected $fillable = [
        'name', 'phone', 'email', 'designation', 'base_salary', 'join_date', 'status'
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function advances()
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
