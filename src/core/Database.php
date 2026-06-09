<?php

namespace MotoristaCheck\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci,
                                                      SESSION time_zone = '-03:00'",
                ]);
            } catch (PDOException $e) {
                if (APP_DEBUG) {
                    die("Erro de conexão: " . $e->getMessage());
                }
                die("Erro ao conectar ao banco de dados.");
            }
        }
        return self::$instance;
    }

    public static function close(): void
    {
        self::$instance = null;
    }
}
