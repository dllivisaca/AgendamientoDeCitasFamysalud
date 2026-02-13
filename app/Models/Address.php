<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'name',
        'full_address',
        'reference',
        'city',
        'google_maps_url',
        'status',
    ];
}