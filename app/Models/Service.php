<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Address;

class Service extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_presential' => 'boolean',
        'is_virtual' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class);
    }


}
