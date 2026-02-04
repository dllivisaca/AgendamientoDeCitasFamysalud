<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Appointment extends Model
{
    use SoftDeletes;
   protected $fillable = [
        // Relaciones
        'user_id',
        'employee_id',
        'service_id',
        'booking_id',

        // Paciente
        'patient_full_name',
        'patient_dob',
        'patient_doc_type',
        'patient_doc_number',
        'patient_email',
        'patient_phone',
        'patient_address',
        'patient_notes',
        'patient_timezone',
        'patient_timezone_label',

        // Facturación
        'billing_name',
        'billing_doc_type',
        'billing_doc_number',
        'billing_email',
        'billing_phone',
        'billing_address',

        // Pago
        'amount',
        'amount_paid',
        'payment_method',
        'amount_standard',
        'discount_amount',
        'payment_status',
        'payment_channel',
        'payment_paid_at',
        'payment_paid_at_date_source',
        'payment_notes',

        // Reembolso
        'amount_refunded',
        'refunded_at',
        'refund_reason',
        'refund_reason_other',
        
        // Cita
        'appointment_date',
        'appointment_time',
        'appointment_end_time',
        'appointment_mode',
        'status',
        'appointment_channel',
        'appointment_request_source',

        // Consentimientos
        'data_consent',
        'terms_accepted',
        'terms_accepted_at',

        // Transferencia
        'transfer_bank_origin',
        'transfer_payer_name',
        'transfer_date',
        'transfer_reference',
        'transfer_receipt_path',

        'transfer_validation_status',
        'transfer_validated_at',
        'transfer_validated_by',
        'transfer_validation_notes',
    ];

    protected $casts = [
        'terms_accepted' => 'boolean',
        'data_consent' => 'boolean',
        'terms_accepted_at' => 'datetime',
        'transfer_date' => 'date',
        'transfer_validated_at' => 'datetime',
        'payment_paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transferValidatedBy()
    {
        return $this->belongsTo(User::class, 'transfer_validated_by');
    }
}
