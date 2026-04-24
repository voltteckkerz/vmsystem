<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    //
    protected $fillable = ['company_id', 'name', 'nric_passport'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    public function visits()
    {
        return $this->belongsToMany(Visit::class, 'visit_visitors')
            ->withPivot('pass_id')
            ->withTimestamps();
    }
}
