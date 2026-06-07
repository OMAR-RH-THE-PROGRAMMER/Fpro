<?php
require_once __DIR__ . '/BaseModel.php';

class PrescriptionModel extends BaseModel {

    public function findByAppointmentId(int $apptId): ?array {
        $result = $this->execute(
            'SELECT * FROM prescriptions WHERE appointment_id = ? LIMIT 1',
            'i',
            [$apptId]
        );
        return $this->fetchOne($result);
    }

    public function create(array $data): int {
        $this->execute(
            'INSERT INTO prescriptions (appointment_id, diagnosis, medications, notes, file_path)
             VALUES (?, ?, ?, ?, ?)',
            'issss',
            [
                $data['appointment_id'],
                $data['diagnosis'],
                $data['medications'],
                $data['notes'] ?? null,
                $data['file_path'] ?? null,
            ]
        );
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $result = $this->execute(
            'UPDATE prescriptions SET diagnosis = ?, medications = ?, notes = ?, file_path = ? WHERE id = ?',
            'ssssi',
            [
                $data['diagnosis'],
                $data['medications'],
                $data['notes'] ?? null,
                $data['file_path'] ?? null,
                $id,
            ]
        );
        return (bool) $result;
    }

    public function getByPatient(int $patientId): array {
        $result = $this->execute(
            'SELECT pr.*, a.appt_date, a.appt_time, d_user.name AS doctor_name, s.name AS specialization_name
             FROM prescriptions pr
             JOIN appointments a ON a.id = pr.appointment_id
             JOIN doctors doc ON doc.id = a.doctor_id
             JOIN users d_user ON d_user.id = doc.user_id
             JOIN specializations s ON s.id = doc.specialization_id
             WHERE a.patient_id = ?
             ORDER BY a.appt_date DESC',
            'i',
            [$patientId]
        );
        return $this->fetchAll($result);
    }

    public function findById(int $id): ?array {
        $result = $this->execute(
            'SELECT pr.*, a.patient_id, a.doctor_id
             FROM prescriptions pr
             JOIN appointments a ON a.id = pr.appointment_id
             WHERE pr.id = ? LIMIT 1',
            'i',
            [$id]
        );
        return $this->fetchOne($result);
    }

    public function countByPatient(int $patientId): int {
        $result = $this->execute(
            'SELECT COUNT(*) as total FROM prescriptions pr
             JOIN appointments a ON a.id = pr.appointment_id
             WHERE a.patient_id = ?',
            'i',
            [$patientId]
        );
        $row = $this->fetchOne($result);
        return (int) ($row['total'] ?? 0);
    }
}
