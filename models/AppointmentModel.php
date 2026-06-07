<?php
require_once __DIR__ . '/BaseModel.php';

class AppointmentModel extends BaseModel {

    public function book(array $data): bool {
        $result = $this->execute(
            'INSERT INTO appointments (patient_id, doctor_id, appt_date, appt_time, reason, status)
             VALUES (?, ?, ?, ?, ?, "pending")',
            'iisss',
            [
                $data['patient_id'],
                $data['doctor_id'],
                $data['appt_date'],
                $data['appt_time'],
                $data['reason'] ?? null,
            ]
        );
        return (bool) $result;
    }

    public function hasConflict(int $doctorId, string $date, string $time): bool {
        $result = $this->execute(
            'SELECT id FROM appointments WHERE doctor_id = ? AND appt_date = ? AND appt_time = ? AND status != "cancelled" LIMIT 1',
            'iss',
            [$doctorId, $date, $time]
        );
        return $this->fetchOne($result) !== null;
    }

    private function buildFilters(array $filters): array {
        $conditions = [];
        $types      = '';
        $params     = [];

        if (!empty($filters['status'])) {
            $conditions[] = 'a.status = ?';
            $types .= 's';
            $params[] = $filters['status'];
        }
        if (!empty($filters['doctor_id'])) {
            $conditions[] = 'a.doctor_id = ?';
            $types .= 'i';
            $params[] = $filters['doctor_id'];
        }
        if (!empty($filters['patient_name'])) {
            $conditions[] = 'p.name LIKE ?';
            $types .= 's';
            $params[] = '%' . $filters['patient_name'] . '%';
        }
        if (!empty($filters['start_date'])) {
            $conditions[] = 'a.appt_date >= ?';
            $types .= 's';
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $conditions[] = 'a.appt_date <= ?';
            $types .= 's';
            $params[] = $filters['end_date'];
        }
        return [$conditions, $types, $params];
    }

    public function getByPatient(int $patientId, int $page, array $filters = []): array {
        $perPage = ITEMS_PER_PAGE;
        $offset  = ($page - 1) * $perPage;

        [$conditions, $types, $params] = $this->buildFilters($filters);
        $conditions[] = 'a.patient_id = ?';
        $types .= 'i';
        $params[] = $patientId;

        $where  = 'WHERE ' . implode(' AND ', $conditions);
        $types .= 'ii';
        $params[] = $perPage;
        $params[] = $offset;

        $result = $this->execute(
            "SELECT a.*, d.name AS doctor_name, s.name AS specialization_name,
                    pr.id AS prescription_id
             FROM appointments a
             JOIN users d ON d.id = (SELECT user_id FROM doctors WHERE id = a.doctor_id)
             JOIN doctors doc ON doc.id = a.doctor_id
             JOIN specializations s ON s.id = doc.specialization_id
             LEFT JOIN prescriptions pr ON pr.appointment_id = a.id
             $where ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ? OFFSET ?",
            $types,
            $params
        );
        return $this->fetchAll($result);
    }

    public function getByDoctor(int $doctorId, int $page, array $filters = []): array {
        $perPage = ITEMS_PER_PAGE;
        $offset  = ($page - 1) * $perPage;

        [$conditions, $types, $params] = $this->buildFilters($filters);
        $conditions[] = 'a.doctor_id = ?';
        $types .= 'i';
        $params[] = $doctorId;

        $where  = 'WHERE ' . implode(' AND ', $conditions);
        $types .= 'ii';
        $params[] = $perPage;
        $params[] = $offset;

        $result = $this->execute(
            "SELECT a.*, p.name AS patient_name, pr.id AS prescription_id
             FROM appointments a
             JOIN users p ON p.id = a.patient_id
             LEFT JOIN prescriptions pr ON pr.appointment_id = a.id
             $where ORDER BY a.appt_date ASC, a.appt_time ASC LIMIT ? OFFSET ?",
            $types,
            $params
        );
        return $this->fetchAll($result);
    }

    public function getTodayByDoctor(int $doctorId): array {
        $result = $this->execute(
            "SELECT a.*, p.name AS patient_name
             FROM appointments a
             JOIN users p ON p.id = a.patient_id
             WHERE a.doctor_id = ? AND a.appt_date = CURDATE() AND a.status != 'cancelled'
             ORDER BY a.appt_time ASC",
            'i',
            [$doctorId]
        );
        return $this->fetchAll($result);
    }

    public function getAll(int $page, array $filters = []): array {
        $perPage = ITEMS_PER_PAGE;
        $offset  = ($page - 1) * $perPage;

        [$conditions, $types, $params] = $this->buildFilters($filters);
        $where  = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $types .= 'ii';
        $params[] = $perPage;
        $params[] = $offset;

        $result = $this->execute(
            "SELECT a.*, p.name AS patient_name,
                    d_user.name AS doctor_name, s.name AS specialization_name
             FROM appointments a
             JOIN users p ON p.id = a.patient_id
             JOIN doctors doc ON doc.id = a.doctor_id
             JOIN users d_user ON d_user.id = doc.user_id
             JOIN specializations s ON s.id = doc.specialization_id
             $where ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ? OFFSET ?",
            $types,
            $params
        );
        return $this->fetchAll($result);
    }

    public function countFiltered(string $scope, int $scopeId, array $filters = []): int {
        [$conditions, $types, $params] = $this->buildFilters($filters);

        if ($scope === 'patient') {
            $conditions[] = 'a.patient_id = ?';
            $types .= 'i';
            $params[] = $scopeId;
        } elseif ($scope === 'doctor') {
            $conditions[] = 'a.doctor_id = ?';
            $types .= 'i';
            $params[] = $scopeId;
        }

        $where  = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $result = $this->execute(
            "SELECT COUNT(*) as total FROM appointments a
             JOIN users p ON p.id = a.patient_id
             $where",
            $types,
            $params
        );
        $row = $this->fetchOne($result);
        return (int) ($row['total'] ?? 0);
    }

    public function updateStatus(int $id, string $status, string $notes = ''): bool {
        $result = $this->execute(
            'UPDATE appointments SET status = ?, doctor_notes = ? WHERE id = ?',
            'ssi',
            [$status, $notes ?: null, $id]
        );
        return (bool) $result;
    }

    public function findById(int $id): ?array {
        $result = $this->execute(
            'SELECT a.*, p.name AS patient_name, p.phone AS patient_phone,
                    d_user.name AS doctor_name, s.name AS specialization_name,
                    doc.consultation_fee, doc.id AS doctor_record_id,
                    pr.id AS prescription_id, pr.diagnosis, pr.medications, pr.notes AS presc_notes,
                    pr.file_path AS presc_file
             FROM appointments a
             JOIN users p ON p.id = a.patient_id
             JOIN doctors doc ON doc.id = a.doctor_id
             JOIN users d_user ON d_user.id = doc.user_id
             JOIN specializations s ON s.id = doc.specialization_id
             LEFT JOIN prescriptions pr ON pr.appointment_id = a.id
             WHERE a.id = ? LIMIT 1',
            'i',
            [$id]
        );
        return $this->fetchOne($result);
    }

    public function countToday(): int {
        $result = $this->execute("SELECT COUNT(*) as total FROM appointments WHERE appt_date = CURDATE()");
        $row    = $this->fetchOne($result);
        return (int) ($row['total'] ?? 0);
    }

    public function countByStatusThisWeek(): array {
        $result = $this->execute(
            "SELECT status, COUNT(*) as total FROM appointments
             WHERE WEEK(appt_date) = WEEK(NOW()) AND YEAR(appt_date) = YEAR(NOW())
             GROUP BY status"
        );
        $data = [];
        foreach ($this->fetchAll($result) as $row) {
            $data[$row['status']] = (int) $row['total'];
        }
        return $data;
    }

    public function getRecent(int $limit = 5): array {
        $result = $this->execute(
            "SELECT a.*, p.name AS patient_name, d_user.name AS doctor_name, s.name AS specialization_name
             FROM appointments a
             JOIN users p ON p.id = a.patient_id
             JOIN doctors doc ON doc.id = a.doctor_id
             JOIN users d_user ON d_user.id = doc.user_id
             JOIN specializations s ON s.id = doc.specialization_id
             ORDER BY a.created_at DESC LIMIT ?",
            'i',
            [$limit]
        );
        return $this->fetchAll($result);
    }

    public function countThisMonth(int $doctorId): int {
        $result = $this->execute(
            "SELECT COUNT(*) as total FROM appointments
             WHERE doctor_id = ? AND MONTH(appt_date) = MONTH(NOW()) AND YEAR(appt_date) = YEAR(NOW())",
            'i',
            [$doctorId]
        );
        $row = $this->fetchOne($result);
        return (int) ($row['total'] ?? 0);
    }

    public function getUpcoming(int $doctorId, int $limit = 5): array {
        $result = $this->execute(
            "SELECT a.*, p.name AS patient_name
             FROM appointments a
             JOIN users p ON p.id = a.patient_id
             WHERE a.doctor_id = ? AND a.appt_date >= CURDATE() AND a.status IN ('pending','confirmed')
             ORDER BY a.appt_date ASC, a.appt_time ASC LIMIT ?",
            'ii',
            [$doctorId, $limit]
        );
        return $this->fetchAll($result);
    }

    public function getPatientStats(int $patientId): array {
        $result = $this->execute(
            "SELECT
                SUM(status IN ('pending','confirmed')) AS active,
                SUM(status = 'completed') AS completed
             FROM appointments WHERE patient_id = ?",
            'i',
            [$patientId]
        );
        return $this->fetchOne($result) ?? ['active' => 0, 'completed' => 0];
    }

    public function getNextForPatient(int $patientId): ?array {
        $result = $this->execute(
            "SELECT a.*, d_user.name AS doctor_name, s.name AS specialization_name
             FROM appointments a
             JOIN doctors doc ON doc.id = a.doctor_id
             JOIN users d_user ON d_user.id = doc.user_id
             JOIN specializations s ON s.id = doc.specialization_id
             WHERE a.patient_id = ? AND a.appt_date >= CURDATE() AND a.status IN ('pending','confirmed')
             ORDER BY a.appt_date ASC, a.appt_time ASC LIMIT 1",
            'i',
            [$patientId]
        );
        return $this->fetchOne($result);
    }

    public function getLast14DaysStats(): array {
        $result = $this->execute(
            "SELECT appt_date, COUNT(*) as total FROM appointments
             WHERE appt_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
             GROUP BY appt_date ORDER BY appt_date"
        );
        return $this->fetchAll($result);
    }

    public function getForReport(array $filters): array {
        $conditions = [];
        $types      = '';
        $params     = [];

        if (!empty($filters['start_date'])) {
            $conditions[] = 'a.appt_date >= ?';
            $types .= 's';
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $conditions[] = 'a.appt_date <= ?';
            $types .= 's';
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['doctor_id'])) {
            $conditions[] = 'a.doctor_id = ?';
            $types .= 'i';
            $params[] = $filters['doctor_id'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = 'a.status = ?';
            $types .= 's';
            $params[] = $filters['status'];
        }

        $where  = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $result = $this->execute(
            "SELECT a.*, p.name AS patient_name, d_user.name AS doctor_name, s.name AS specialization_name
             FROM appointments a
             JOIN users p ON p.id = a.patient_id
             JOIN doctors doc ON doc.id = a.doctor_id
             JOIN users d_user ON d_user.id = doc.user_id
             JOIN specializations s ON s.id = doc.specialization_id
             $where ORDER BY a.appt_date ASC",
            $types,
            $params
        );
        return $this->fetchAll($result);
    }
}
