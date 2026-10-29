<?php

declare(strict_types=1);

return [

    'view' => [
        'prefix' => 'back.',

        'name-homepage' => 'home',

        'resources-index'  => 'index',
        'resources-create' => 'create',
        'resources-update' => 'edit',
        'resources-read'   => 'show',
    ],

    'route' => [

        'prefix' => 'bo.',

        'action-create' => 'store',
        'action-update' => 'update',
        'action-delete' => 'destroy',
    ],

    /*
        |--------------------------------------------------------------------------
        | Model field used for an update
        |--------------------------------------------------------------------------
        |
        | Here you can configure the list of possible text fields existing in a model,
        | (the first one to exist will be updated, must be a text field).
        | ex: ['name', 'label', 'title', 'first_name']
        |
     */

    'list-fields' => [
        'name',
        'label',
        'title',
        'first_name',
    ],
];
