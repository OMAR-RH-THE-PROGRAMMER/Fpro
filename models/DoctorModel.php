<?php
require_once __DIR__ . '/BaseModel.php';

class DoctorModel extends BaseModel {

    public function findByUserId(int $userId): ?array {
        $result = $this->execute(
            'SELECT d.*, u.name, u.email, u.phone, u.avatar, s.name AS specialization_name
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE d.user_id = ? LIMIT 1',
            'i',
            [$userId]
        );
        return $this->fetchOne($result);
    }

    public function findById(int $doctorId): ?array {
        $result = $this->execute(
            'SELECT d.*, u.name, u.email, u.phone, u.avatar, s.name AS specialization_name
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             WHERE d.id = ? LIMIT 1',
            'i',
            [$doctorId]
        );
        return $this->fetchOne($result);
    }

    public function getAll(): array {
        $result = $this->execute(
            'SELECT d.*, u.name, u.email, s.name AS specialization_name
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             ORDER BY u.name'
        );
        return $this->fetchAll($result);
    }

    public function getAllPaginated(int $page): array {
        $perPage = ITEMS_PER_PAGE;
        $offset  = ($page - 1) * $perPage;
        $result  = $this->execute(
            'SELECT d.*, u.name, u.email, u.is_active, s.name AS specialization_name
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             JOIN specializations s ON s.id = d.specialization_id
             ORDER BY u.name LIMIT ? OFFSET ?',
            'ii',
            [$perPage, $offset]
        );
        return $this->fetchAll($result);
    }

    public function countAll(): int {
        $result = $this->execute('SELECT COUNT(*) as total FROM doctors');
        $row    = $this->fetchOne($result);
        return (int) ($row['total'] ?? 0);
    }

    public function create(array $data): int {
        $this->execute(
            'INSERT INTO doctors (user_id, specialization_id, bio, consultation_fee, available_days)
             VALUES (?, ?, ?, ?, ?)',
            'iisds',
            [
                $data['user_id'],
                $data['specialization_id'],
                $data['bio'] ?? null,
                $data['consultation_fee'] ?? 0.00,
                $data['available_days'] ?? 'Sun,Mon,Tue,Wed,Thu',
            ]
        );
        return $this->db->lastInsertId();
    }

    public function update(int $doctorId, array $data): bool {
        $result = $this->execute(
            'UPDATE doctors SET specialization_id = ?, bio = ?, consultation_fee = ?, available_days = ?
             WHERE id = ?',
            'isdsi',
            [
                $data['specialization_id'],
                $data['bio'] ?? null,
                $data['consultation_fee'] ?? 0.00,
                $data['available_days'] ?? 'Sun,Mon,Tue,Wed,Thu',
                $doctorId,
            ]
        );
        return (bool) $result;
    }

    public function getAvailableDays(int $doctorId): array {
        $result = $this->execute(
            'SELECT available_days FROM doctors WHERE id = ? LIMIT 1',
            'i',
            [$doctorId]
        );
        $row = $this->fetchOne($result);
        if (!$row) return [];
        return array_map('trim', explode(',', $row['available_days']));
    }
}
