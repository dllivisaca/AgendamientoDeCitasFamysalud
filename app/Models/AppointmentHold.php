<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentHold extends Model
{
    protected $table = 'appointment_holds';
    public $timestamps = false; // porque tu tabla tiene created_at pero no updated_at (si quieres, lo activamos)

    protected $fillable = [
        'employee_id',
        'service_id',
        'appointment_date',
        'appointment_time',
        'appointment_end_time',
        'session_id',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}