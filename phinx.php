<?php

$dbConfig = require_once __DIR__ . '/config/database.php';
$defaultConnection = $dbConfig['default'];
$connectionConfig = $dbConfig['connections'][$defaultConnection];

return
    [
        'paths' => [
            "migrations" => "database/migrations",
            "seeds"      => "database/seeds"
        ],
        'environments' => [
            'default_migration_table' => 'migrations',
            'default_environment' => 'dev',
            'dev' => [
                "adapter" => $connectionConfig['driver'],
                "host" => $connectionConfig['host'],
                "name" => $connectionConfig['database'],
                "user" => $connectionConfig['username'],
                "pass" => $connectionConfig['password'],
                "port" => $connectionConfig['port'],
                "charset" => $connectionConfig['charset']
            ],
        ],
        'version_order' => 'creation'
    ];
