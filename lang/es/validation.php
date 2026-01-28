<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'after_or_equal' => 'La fecha de :attribute debe ser igual o posterior a :date.',

    'in' => 'El campo :attribute contiene un valor inválido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        // ✅ Ajusta el nombre exacto del input/field que estás validando:
        'transfer_date' => 'la fecha del comprobante de transferencia',
        'payment_status' => 'el estado del pago',
        'payment_method' => 'el método de pago',
        'status' => 'estado de la cita',
        // si tu campo tiene otro nombre, agrégalo aquí también:
        // 'fecha_transferencia' => 'la fecha del comprobante de transferencia',
    ],
];