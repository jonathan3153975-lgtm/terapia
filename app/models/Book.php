<?php

namespace App\Models;

use Classes\Model;

class Book extends Model
{
    protected string $table = 'books';

    public function listByTherapist(int $therapistId, string $search = ''): array
    {
        $sql = 'SELECT b.*, COUNT(DISTINCT f.id) AS favorite_count
                FROM books b
                LEFT JOIN patient_book_favorites f ON f.book_id = b.id
                WHERE b.therapist_id = ?';
        $params = [$therapistId];

        if ($search !== '') {
            $sql .= ' AND b.title LIKE ?';
            $params[] = '%' . $search . '%';
        }

        $sql .= ' GROUP BY b.id ORDER BY b.created_at DESC, b.id DESC';
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findByTherapistAndId(int $therapistId, int $bookId): ?array
    {
        $stmt = $this->query(
            'SELECT b.*, COUNT(DISTINCT f.id) AS favorite_count
             FROM books b
             LEFT JOIN patient_book_favorites f ON f.book_id = b.id
             WHERE b.therapist_id = ? AND b.id = ?
             GROUP BY b.id
             LIMIT 1',
            [$therapistId, $bookId]
        );
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByTherapistAndId(int $therapistId, int $bookId): bool
    {
        return (bool) $this->query('DELETE FROM books WHERE therapist_id = ? AND id = ?', [$therapistId, $bookId]);
    }

    public function listPublishedByTherapist(int $therapistId, string $search = ''): array
    {
        $sql = 'SELECT * FROM books WHERE therapist_id = ? AND is_published = 1 AND pdf_path IS NOT NULL AND pdf_path <> ""';
        $params = [$therapistId];

        if ($search !== '') {
            $sql .= ' AND title LIKE ?';
            $params[] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY updated_at DESC, created_at DESC, id DESC';
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function findPublishedByPatientAndId(int $patientId, int $bookId): ?array
    {
        $stmt = $this->query(
            'SELECT b.*
             FROM books b
             INNER JOIN patients p ON p.therapist_id = b.therapist_id
             WHERE p.id = ? AND b.id = ? AND b.is_published = 1 AND b.pdf_path IS NOT NULL AND b.pdf_path <> ""
             LIMIT 1',
            [$patientId, $bookId]
        );
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listFavoriteBooksByPatient(int $patientId, string $search = ''): array
    {
        $sql = 'SELECT b.*, f.created_at AS favorited_at
                FROM patient_book_favorites f
                INNER JOIN books b ON b.id = f.book_id
            WHERE f.patient_id = ? AND b.is_published = 1 AND b.pdf_path IS NOT NULL AND b.pdf_path <> ""';
        $params = [$patientId];

        if ($search !== '') {
            $sql .= ' AND b.title LIKE ?';
            $params[] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY f.created_at DESC, b.title ASC';
        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }
}