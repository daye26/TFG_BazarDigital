<?php

return [
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña actual no es correcta.',
    'email' => 'El campo :attribute debe ser un correo electrónico válido.',
    'lowercase' => 'El campo :attribute debe estar en minúsculas.',
    'max' => [
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'regex' => 'El formato de :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'El :attribute ya ha sido registrado.',

    'custom' => [
        'email' => [
            'email' => 'Introduce un correo electrónico válido.',
            'required' => 'El correo electrónico es obligatorio.',
            'unique' => 'Ese correo electrónico ya está registrado.',
        ],
        'name' => [
            'required' => 'El nombre es obligatorio.',
        ],
        'password' => [
            'confirmed' => 'La confirmación de la contraseña no coincide.',
            'min' => 'La contraseña debe tener al menos :min caracteres.',
            'required' => 'La contraseña es obligatoria.',
        ],
        'phone' => [
            'required' => 'El teléfono es obligatorio.',
            'unique' => 'El teléfono ya ha sido registrado.',
        ],
        'phone_country_code' => [
            'regex' => 'El prefijo del teléfono debe empezar por + y tener entre 1 y 3 dígitos.',
            'required' => 'El prefijo del teléfono es obligatorio.',
        ],
        'phone_number' => [
            'regex' => 'El número de teléfono debe contener entre 2 y 14 dígitos.',
            'required' => 'El número de teléfono es obligatorio.',
        ],
    ],

    'attributes' => [
        'current_password' => 'contraseña actual',
        'email' => 'correo electrónico',
        'name' => 'nombre',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de la contraseña',
        'phone' => 'teléfono',
        'phone_country_code' => 'prefijo del teléfono',
        'phone_number' => 'número de teléfono',
        'token' => 'token',
    ],
];
