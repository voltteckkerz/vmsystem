<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    //
    protected $fillable = ['employee_id', 'purpose', 'remarks', 'manual_check_in_time', 'manual_check_out_time', 'status'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function visitors()
    {
        return $this->belongsToMany(Visitor::class, 'visit_visitors')
            ->withPivot('pass_id')
            ->withTimestamps();
    }
}
