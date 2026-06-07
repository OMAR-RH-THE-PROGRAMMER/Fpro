<?php
require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel {

    public function findById(int $id): ?array {
        $result = $this->execute('SELECT * FROM users WHERE id = ? LIMIT 1', 'i', [$id]);
        return $this->fetchOne($result);
    }

    public function findByEmail(string $email): ?array {
        $result = $this->execute('SELECT * FROM users WHERE email = ? LIMIT 1', 's', [$email]);
        return $this->fetchOne($result);
    }

    /**
     * Create a new user. Password must already be hashed.
     */
    public function create(array $data): int {
        $this->execute(
            'INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)',
            'sssss',
            [
                $data['name'],
                $data['email'],
                $data['password'],
                $data['role'] ?? 'patient',
                $data['phone'] ?? null,
            ]
        );
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $result = $this->execute(
            'UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?',
            'sssi',
            [$data['name'], $data['phone'] ?? null, $data['avatar'] ?? null, $id]
        );
        return (bool) $result;
    }

    public function updatePassword(int $id, string $newHash): bool {
        $result = $this->execute('UPDATE users SET password = ? WHERE id = ?', 'si', [$newHash, $id]);
        return (bool) $result;
    }

    public function getAllPaginated(int $page, string $role = '', string $search = ''): array {
        $perPage = ITEMS_PER_PAGE;
        $offset  = ($page - 1) * $perPage;
        $conditions = [];
        $types  = '';
        $params = [];

        if ($role !== '') {
            $conditions[] = 'role = ?';
            $types .= 's';
            $params[] = $role;
        }
        if ($search !== '') {
            $conditions[] = '(name LIKE ? OR email LIKE ?)';
            $types .= 'ss';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $types .= 'ii';
        $params[] = $perPage;
        $params[] = $offset;

        $result = $this->execute(
            "SELECT id, name, email, role, phone, is_active, created_at FROM users $where ORDER BY id DESC LIMIT ? OFFSET ?",
            $types,
            $params
        );
        return $this->fetchAll($result);
    }

    public function countAll(string $role = '', string $search = ''): int {
        $conditions = [];
        $types  = '';
        $params = [];

        if ($role !== '') {
            $conditions[] = 'role = ?';
            $types .= 's';
            $params[] = $role;
        }
        if ($search !== '') {
            $conditions[] = '(name LIKE ? OR email LIKE ?)';
            $types .= 'ss';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $where  = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $result = $this->execute("SELECT COUNT(*) as total FROM users $where", $types, $params);
        $row    = $this->fetchOne($result);
        return (int) ($row['total'] ?? 0);
    }

    public function toggleActive(int $id): bool {
        $result = $this->execute(
            'UPDATE users SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?',
            'i',
            [$id]
        );
        return (bool) $result;
    }

    public function countByRole(): array {
        $result = $this->execute('SELECT role, COUNT(*) as total FROM users GROUP BY role');
        $data   = [];
        foreach ($this->fetchAll($result) as $row) {
            $data[$row['role']] = (int) $row['total'];
        }
        return $data;
    }
}
