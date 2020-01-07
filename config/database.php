<?php

return [

    'default' => 'mongodb',

    'connections' => [

        'mongodb' => array(
            'driver'   => 'mongodb',
            'host'     => 'localhost',
            'port'     => 27017,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'database' => env('DB_DATABASE','notes_service')
        ),

    ],

    'migrations' => 'migrations',
];