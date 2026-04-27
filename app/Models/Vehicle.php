<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['plate_number', 'owner_type'];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employees_vehicles');
    }
}
