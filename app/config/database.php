<?php
/**
 * app/config/database.php
 * Database connection using PDO (PHP 8.3)
 */

class Database
{
    private string $host = 'localhost';
    private string $db_name = 'payroll_system';
    private string $username = 'root';
    private string $password = '';
    private string $charset = 'utf8mb4';

    private ?PDO $conn = null;

    public function connect(): PDO
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            return $this->conn;

        } catch (PDOException $e) {
            // Jangan tampilkan error detail di production
            die('Database connection failed.');
        }
    }
}
