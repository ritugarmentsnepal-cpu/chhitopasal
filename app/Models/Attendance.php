<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use \App\Traits\Loggable;

    protected $fillable = [
        'employee_id', 'date', 'status', 'notes'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
