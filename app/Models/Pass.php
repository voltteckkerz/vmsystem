<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pass extends Model
{
    //
    protected $fillable = ['pass_number', 'status'];

    public function visitors()
    {
        return $this->belongsToMany(Visitor::class, 'visit_visitors')
            ->withPivot('visit_id')
            ->withTimestamps();
    }
}
