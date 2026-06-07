<?php
require_once __DIR__ . '/../core/Database.php';

abstract class BaseModel {
    protected Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Execute a prepared statement via the database.
     */
    protected function execute(string $sql, string $types = '', array $params = []): mixed {
        return $this->db->query($sql, $types, $params);
    }

    /**
     * Fetch all rows from a result set as associative array.
     */
    protected function fetchAll(mixed $result): array {
        if (!$result instanceof mysqli_result) return [];
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Fetch a single row from a result set.
     */
    protected function fetchOne(mixed $result): ?array {
        if (!$result instanceof mysqli_result) return null;
        return $result->fetch_assoc() ?: null;
    }
}
