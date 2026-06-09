<?php

require_once __DIR__ . '/app.php';

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    public static function config(array $cfg = []): void
    {
        self::$config = $cfg;
    }

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $host = self::$config['host'] ?? DB_HOST;
            $port = self::$config['port'] ?? DB_PORT;
            $name = self::$config['name'] ?? DB_NAME;
            $user = self::$config['user'] ?? DB_USER;
            $pass = self::$config['pass'] ?? DB_PASS;

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
        }

        return self::$instance;
    }

    public static function close(): void
    {
        self::$instance = null;
    }
}
