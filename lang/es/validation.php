<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'required'  => 'El campo :attribute es obligatorio.',
    'regex' => 'El campo :attribute debe contener al menos una letra mayúscula, una letra minúscula, un número y un símbolo.',
    'unique'    => 'El valor de :attribute ya está registrado.',
    'email'     => 'El campo :attribute debe ser un correo electrónico válido.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'min'       => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'max'       => [
        'string' => 'El campo :attribute no puede superar :max caracteres.',
    ],
    'boolean'   => 'El campo :attribute debe ser verdadero o falso.',
    'in'        => 'El campo :attribute contiene un valor inválido.',
    'after_or_equal' => 'La fecha de :attribute debe ser igual o posterior a :date.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'password' => [
            'regex' => 'La contraseña debe tener mínimo 8 caracteres e incluir: una mayúscula, una minúscula, un número y un símbolo (@$!%*#?&._-).',
        ],
    ],

    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'phone' => 'teléfono',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
        'roles' => 'rol del usuario',

        // los que ya tenías 👇
        'transfer_date' => 'la fecha del comprobante de transferencia',
        'payment_status' => 'el estado del pago',
        'payment_method' => 'el método de pago',
        'status' => 'estado de la cita',
    ],
];