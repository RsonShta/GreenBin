<?php

class Database
{
    // Hold the single PDO instance
    private static ?PDO $instance = null;

    // Database connection parameters
    private string $host = 'localhost';
    private string $dbname = 'GreenBin_Nepal';
    private string $user = 'root';
    private string $pass = '';
    private string $charset = 'utf8mb4';

    // Private constructor to prevent direct instantiation
    private function __construct()
    {
    }

    // Get the singleton PDO instance
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $db = new self();
            $dsn = "mysql:host={$db->host};dbname={$db->dbname};charset={$db->charset}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Fetch associative arrays by default
                PDO::ATTR_EMULATE_PREPARES => false,  // Use native prepared statements
            ];

            try {
                self::$instance = new PDO($dsn, $db->user, $db->pass, $options);
            } catch (PDOException $e) {
                error_log("DB Connection failed: " . $e->getMessage());
                die("Database connection error.");  // Stop script if connection fails
            }
        }

        return self::$instance;
    }

    // Prevent cloning the singleton instance
    private function __clone()
    {
    }

    // Prevent unserializing the singleton instance
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
