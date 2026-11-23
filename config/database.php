<?php
declare(strict_types=1);




class Database
{
    private string $host = '127.0.0.1';
    private string $port = '3306';
    private string $database = 'surong_guisad_drrm';
    private string $username = 'root';
    private string $password = '';

    public function connect(): PDO
    {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";

        return new PDO($dsn, $this->username, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}




function db(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $database = new Database();
    $connection = $database->connect();

    return $connection;
}