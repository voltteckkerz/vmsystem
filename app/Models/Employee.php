<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['name', 'status'];

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'employees_vehicles');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    
}
