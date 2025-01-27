<?php
/**
 * Similar to DB class, but supports entering custom database credentials.
 * Supports PostgresSQL (Requires php-pgsql and php-pdo_pgsql)
 *
 * @package NamelessMC\Database
 * @see DB
 * @see InteractsWithDatabase
 * @author RiceCX
 * @version 2.0.0-pr8
 * @license MIT
 */
class DB_PGCustom {

    use InteractsWithDatabase;

    private static DB_Custom $_instance;

    public function __construct(string $host, string $database, string $username, string $password, int $port = 3306, string $prefix = '') {
        try {
            $this->_pdo = new PDO(
                'pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $database,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
                ]
            );
            $this->_prefix = $prefix;
        } catch (PDOException $e) {
            die("<strong>Error:<br /></strong><div class=\"alert alert-danger\">" . $e->getMessage() . '</div>Please check your database connection settings.');
        }

        $this->_query_recorder = QueryRecorder::getInstance();
    }

    public static function getInstance(string $host, string $database, string $username, string $password, int $port = 3306, string $prefix = ''): DB_Custom {
        return self::$_instance ??= new DB_Custom($host, $database, $username, $password, $port, $prefix);
    }
}
