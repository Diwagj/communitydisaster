<?php
declare(strict_types=1);

/**
 * Returns the application's shared PDO connection.
 *
 * All database access in this project should call db().
 * This is the only database connection helper used by the application.
 */
function db(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = '127.0.0.1';
    $port = '3306';
    $database = 'surong_guisad_drrm';
    $username = 'root';
    $password = '';

    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

    $connection = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}
