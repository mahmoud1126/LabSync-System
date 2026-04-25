<?php

class Database
{
    private static $instance = null;
    private $pdo;

    private $host = "localhost";
    private $username = "root";
    private $password = "123456";
    private $dbname = "LabSyncDB";


    // Private constructor → Singleton
    private function __construct()
    {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password
            );

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    // Public static getter → always returns same instance
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Getter for PDO
    public function getConnection()
    {
        return $this->pdo;
    }
}