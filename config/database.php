<?php
/**
 * Database Configuration
 * Đọc từ $_ENV — không hardcode credentials ở đây
 */

return [
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST']    ?? '127.0.0.1',
    'port'      => $_ENV['DB_PORT']    ?? '3306',
    'database'  => $_ENV['DB_NAME']    ?? 'sportzone',
    'username'  => $_ENV['DB_USER']    ?? 'root',
    'password'  => $_ENV['DB_PASS']    ?? '',
    'charset'   => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'timezone'  => '+07:00',

    // PDO Options
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone='+07:00'",
    ],
];
