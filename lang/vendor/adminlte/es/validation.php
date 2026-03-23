<?php

return [

    'required' => 'Falta llenar :attribute.',
    'email'    => 'El campo :attribute debe ser un correo válido.',
    'max'      => 'El campo :attribute no debe superar :max caracteres.',
    'unique'   => 'El valor de :attribute ya está registrado.',

    'attributes' => [
        'name'            => 'el nombre de la sucursal',
        'code'            => 'el código',
        'address'         => 'la dirección',
        'phone'           => 'el teléfono',
        'email'           => 'el email',
        'manager_user_id' => 'el responsable',
        'is_active'       => 'el estado',
    ],
];
