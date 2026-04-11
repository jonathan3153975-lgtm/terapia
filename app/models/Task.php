<?php

namespace App\Models;

use Classes\Model;

class Task extends Model
{
    protected string $table = 'tasks';

    private function normalizedDeliveryKind(string $deliveryKind): string
    {
        return $deliveryKind === 'material' ? 'material' : 'task';
    }

    public function countByTherapist(int $therapistId): int
    {
        return $this->count('therapist_id = ?', [$therapistId]);
    }

    public function countCreatedInMonthByTherapist(int $therapistId, string $yearMonth): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) AS total
             FROM tasks
             WHERE therapist_id = ?
               AND DATE_FORMAT(created_at, '%Y-%m') = ?",
            [$therapistId, $yearMonth]
        );
        if (!$stmt) {
            return 0;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function listByPatient(int $patientId): array
    {
        $stmt = $this->query(
            'SELECT t.*, m.title AS material_title, m.type AS material_type
             FROM tasks t
             LEFT JOIN materials m ON m.id = t.material_id
             WHERE t.patient_id = ?
             ORDER BY t.due_date DESC',
            [$patientId]
        );
        if (!$stmt) {
            return [];
        }
        return $stmt->fetchAll();
    }

    public function countPendingByPatient(int $patientId): int
    {
        return $this->count("patient_id = ? AND status = 'pending'", [$patientId]);
    }

    public function countDoneByPatient(int $patientId): int
    {
        return $this->count("patient_id = ? AND status = 'done'", [$patientId]);
    }

    public function countDoneInMonthByPatient(int $patientId, string $yearMonth): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) AS total
             FROM tasks
             WHERE patient_id = ?
               AND status = 'done'
               AND DATE_FORMAT(updated_at, '%Y-%m') = ?",
            [$patientId, $yearMonth]
        );
        if (!$stmt) {
            return 0;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countInboxByPatientAndKind(int $patientId, string $deliveryKind): int
    {
        $kind = $this->normalizedDeliveryKind($deliveryKind);

        $stmt = $this->query(
            'SELECT COUNT(*) AS total
             FROM tasks
             WHERE patient_id = ?
               AND send_to_patient = 1
               AND COALESCE(delivery_kind, "task") = ?',
            [$patientId, $kind]
        );

        if (!$stmt) {
            return 0;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countPendingInboxTasksByPatient(int $patientId): int
    {
        $stmt = $this->query(
            'SELECT COUNT(*) AS total
             FROM tasks
             WHERE patient_id = ?
               AND send_to_patient = 1
               AND status = "pending"
               AND COALESCE(delivery_kind, "task") = "task"',
            [$patientId]
        );

        if (!$stmt) {
            return 0;
        }

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function listInboxByPatientAndKind(int $patientId, string $deliveryKind): array
    {
        $kind = $this->normalizedDeliveryKind($deliveryKind);

        $stmt = $this->query(
            'SELECT t.*, m.title AS material_title, m.type AS material_type
             FROM tasks t
             LEFT JOIN materials m ON m.id = t.material_id
             WHERE t.patient_id = ?
               AND t.send_to_patient = 1
               AND COALESCE(t.delivery_kind, "task") = ?
             ORDER BY t.due_date DESC, t.id DESC',
            [$patientId, $kind]
        );
        if (!$stmt) {
            return [];
        }
        return $stmt->fetchAll();
    }

    public function findInboxTaskByPatientAndId(int $patientId, int $taskId, string $deliveryKind = 'task'): ?array
    {
        $kind = $this->normalizedDeliveryKind($deliveryKind);

        $stmt = $this->query(
            'SELECT t.*, m.title AS material_title, m.type AS material_type
             FROM tasks t
             LEFT JOIN materials m ON m.id = t.material_id
             WHERE t.id = ?
               AND t.patient_id = ?
               AND t.send_to_patient = 1
               AND COALESCE(t.delivery_kind, "task") = ?
             LIMIT 1',
            [$taskId, $patientId, $kind]
        );
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByTherapistPatientAndId(int $therapistId, int $patientId, int $taskId): ?array
    {
        $stmt = $this->query(
            'SELECT t.*, m.title AS material_title, m.type AS material_type
             FROM tasks t
             LEFT JOIN materials m ON m.id = t.material_id
             WHERE t.id = ? AND t.therapist_id = ? AND t.patient_id = ?
             LIMIT 1',
            [$taskId, $therapistId, $patientId]
        );
        if (!$stmt) {
            return null;
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByPatientAndId(int $patientId, int $taskId): ?array
    {
        $stmt = $this->query(
            'SELECT t.*, m.title AS material_title, m.type AS material_type
             FROM tasks t
             LEFT JOIN materials m ON m.id = t.material_id
             WHERE t.id = ? AND t.patient_id = ?
             LIMIT 1',
            [$taskId, $patientId]
        );
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByTherapistPatientAndId(int $therapistId, int $patientId, int $taskId): bool
    {
        return (bool) $this->query(
            'DELETE FROM tasks WHERE id = ? AND therapist_id = ? AND patient_id = ?',
            [$taskId, $therapistId, $patientId]
        );
    }

    public function listLinkedMaterials(int $taskId): array
    {
        $stmt = $this->query(
            'SELECT m.*
             FROM task_material_links tml
             INNER JOIN materials m ON m.id = tml.material_id
             WHERE tml.task_id = ?
             ORDER BY m.title ASC',
            [$taskId]
        );
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function listLinkedMaterialsGroupedByTask(array $taskIds): array
    {
        $taskIds = array_values(array_unique(array_map('intval', $taskIds)));
        $taskIds = array_filter($taskIds, static fn (int $id): bool => $id > 0);
        if (empty($taskIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
        $stmt = $this->query(
            'SELECT tml.task_id, m.*
             FROM task_material_links tml
             INNER JOIN materials m ON m.id = tml.material_id
             WHERE tml.task_id IN (' . $placeholders . ')
             ORDER BY m.title ASC',
            $taskIds
        );
        if (!$stmt) {
            return [];
        }

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[(int) $row['task_id']][] = $row;
        }

        return $grouped;
    }

    public function syncLinkedMaterials(int $taskId, array $materialIds): bool
    {
        $materialIds = array_values(array_unique(array_map('intval', $materialIds)));
        $materialIds = array_filter($materialIds, static fn (int $id): bool => $id > 0);

        $deleted = $this->query('DELETE FROM task_material_links WHERE task_id = ?', [$taskId]);
        if (!$deleted) {
            return false;
        }

        foreach ($materialIds as $materialId) {
            $ok = $this->query(
                'INSERT INTO task_material_links (task_id, material_id, created_at) VALUES (?, ?, ?)',
                [$taskId, $materialId, date('Y-m-d H:i:s')]
            );
            if (!$ok) {
                return false;
            }
        }

        return true;
    }
}
