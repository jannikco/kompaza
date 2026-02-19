<?php

namespace App\Database;

use PDO;
use PDOException;

class Database {
    private static ?PDO $connection = null;

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    DB_HOST,
                    DB_PORT,
                    DB_DATABASE
                );

                self::$connection = new PDO(
                    $dsn,
                    DB_USERNAME,
                    DB_PASSWORD,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                    ]
                );
            } catch (PDOException $e) {
                if (APP_DEBUG) {
                    die("Database connection failed: " . $e->getMessage());
                } else {
                    die("Database connection failed. Please contact administrator.");
                }
            }
        }

        return self::$connection;
    }

    public static function beginTransaction(): bool {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool {
        return self::getConnection()->commit();
    }

    public static function rollback(): bool {
        return self::getConnection()->rollBack();
    }

    public static function lastInsertId(): string {
        return self::getConnection()->lastInsertId();
    }
}
