<?php
require_once __DIR__ . '/BaseModel.php';

class SpecializationModel extends BaseModel {

    public function getAll(): array {
        $result = $this->execute('SELECT * FROM specializations ORDER BY name');
        return $this->fetchAll($result);
    }

    public function findById(int $id): ?array {
        $result = $this->execute('SELECT * FROM specializations WHERE id = ? LIMIT 1', 'i', [$id]);
        return $this->fetchOne($result);
    }

    public function create(string $name): int {
        $this->execute('INSERT INTO specializations (name) VALUES (?)', 's', [$name]);
        return $this->db->lastInsertId();
    }

    public function delete(int $id): bool {
        $result = $this->execute('DELETE FROM specializations WHERE id = ?', 'i', [$id]);
        return (bool) $result;
    }

    public function isSafeToDelete(int $id): bool {
        $result = $this->execute(
            'SELECT COUNT(*) as total FROM doctors WHERE specialization_id = ?',
            'i',
            [$id]
        );
        $row = $this->fetchOne($result);
        return ((int) ($row['total'] ?? 1)) === 0;
    }
}
