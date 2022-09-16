<?php
return [
    'database' => [
        'paths' => [
            'src/Entity',
        ],
        'params' => [
            'driver'   => 'mysqli',
            'host'     => 'mysql',
            'port'     => '3306',
            'user'     => 'admin',
            'password' => 'secret',
            'dbname'   => 'test_db',
        ],
    ],
];