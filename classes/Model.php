<?php

namespace Classes;

use Config\Database;

abstract class Model
{
    protected string $table;
    protected \PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance()->getConnection();
    }

    protected function query(string $sql, array $params = []): \PDOStatement|false
    {
        return Database::getInstance()->query($sql, $params);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1", [$id]);
        if (!$stmt) {
            return null;
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insert(array $data): int|false
    {
        $cols = array_keys($data);
        $vals = array_values($data);
        $marks = implode(',', array_fill(0, count($cols), '?'));
        $sql = "INSERT INTO {$this->table} (" . implode(',', $cols) . ") VALUES ({$marks})";
        $stmt = $this->query($sql, $vals);
        if (!$stmt) {
            return false;
        }
        return (int) $this->connection->lastInsertId();
    }

    public function updateById(int $id, array $data): bool
    {
        $cols = array_keys($data);
        $vals = array_values($data);
        $vals[] = $id;
        $set = implode(',', array_map(fn($c) => "{$c} = ?", $cols));
        $stmt = $this->query("UPDATE {$this->table} SET {$set} WHERE id = ?", $vals);
        if (!$stmt) {
            return false;
        }
        return $stmt->rowCount() > 0;
    }

    public function count(string $where = '1=1', array $params = []): int
    {
        $stmt = $this->query("SELECT COUNT(*) AS total FROM {$this->table} WHERE {$where}", $params);
        if (!$stmt) {
            return 0;
        }
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }
}
