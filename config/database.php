<?php

return [

    'default' => 'mongodb',

    'connections' => [

        'mongodb' => array(
            'driver'   => 'mongodb',
            'host'     => 'localhost',
            'port'     => 27017,
            'username' => '',
            'password' => '',
            'database' => 'notes_service'
        ),

    ],

    'migrations' => 'migrations',
];