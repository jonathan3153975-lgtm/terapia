<?php

namespace App\Models;

use Classes\Model;

class PatientBookFavorite extends Model
{
    protected string $table = 'patient_book_favorites';

    public function exists(int $patientId, int $bookId): bool
    {
        $stmt = $this->query(
            'SELECT id FROM patient_book_favorites WHERE patient_id = ? AND book_id = ? LIMIT 1',
            [$patientId, $bookId]
        );
        if (!$stmt) {
            return false;
        }

        return (bool) $stmt->fetch();
    }

    public function listBookIdsByPatient(int $patientId): array
    {
        $stmt = $this->query('SELECT book_id FROM patient_book_favorites WHERE patient_id = ?', [$patientId]);
        if (!$stmt) {
            return [];
        }

        return array_map(static fn (array $row): int => (int) ($row['book_id'] ?? 0), $stmt->fetchAll());
    }

    public function deleteByPatientAndBook(int $patientId, int $bookId): bool
    {
        return (bool) $this->query('DELETE FROM patient_book_favorites WHERE patient_id = ? AND book_id = ?', [$patientId, $bookId]);
    }

    public function insertIgnore(array $data): bool
    {
        $stmt = $this->query(
            'INSERT IGNORE INTO patient_book_favorites (patient_id, book_id, therapist_id, created_at) VALUES (?, ?, ?, ?)',
            [
                (int) ($data['patient_id'] ?? 0),
                (int) ($data['book_id'] ?? 0),
                (int) ($data['therapist_id'] ?? 0),
                (string) ($data['created_at'] ?? date('Y-m-d H:i:s')),
            ]
        );

        return (bool) $stmt;
    }
}