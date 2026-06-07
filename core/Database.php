<?php
require_once __DIR__ . '/../config/database.php';

class Database {
    private static ?Database $instance = null;
    private mysqli $conn;

    private function __construct() {
        mysqli_report(MYSQLI_REPORT_OFF);
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            error_log('DB connection failed: ' . $this->conn->connect_error);
            throw new RuntimeException('Database connection error. Please try again later.');
        }
        $this->conn->set_charset(DB_CHARSET);
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Execute a prepared statement.
     * Returns result set for SELECT, true/false for INSERT/UPDATE/DELETE.
     */
    public function query(string $sql, string $types = '', array $params = []): mixed {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log('Prepare failed: ' . $this->conn->error . ' | SQL: ' . $sql);
            throw new RuntimeException('Query preparation failed.');
        }
        if ($types !== '' && count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result instanceof mysqli_result) {
            return $result;
        }
        return $stmt->affected_rows >= 0;
    }

    public function lastInsertId(): int {
        return (int) $this->conn->insert_id;
    }

    // Prevent cloning
    private function __clone() {}
}
