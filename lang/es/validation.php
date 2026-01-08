<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'after_or_equal' => 'La fecha de :attribute debe ser igual o posterior a :date.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        // ✅ Ajusta el nombre exacto del input/field que estás validando:
        'transfer_date' => 'la fecha del comprobante de transferencia',
        // si tu campo tiene otro nombre, agrégalo aquí también:
        // 'fecha_transferencia' => 'la fecha del comprobante de transferencia',
    ],
];