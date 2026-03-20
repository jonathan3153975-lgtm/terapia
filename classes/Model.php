<?php

namespace Classes;

use Config\Database;
use PDO;

/**
 * Classe Model base para todos os modelos
 * Abstract class que fornece funcionalidades comuns
 */
abstract class Model
{
    protected Database $database;
    protected PDO $connection;
    protected string $table;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->connection = $this->database->getConnection();
    }

    /**
     * Executa uma query preparada
     */
    protected function query(string $sql, array $params = []): \PDOStatement|false
    {
        return $this->database->query($sql, $params);
    }

    /**
     * Busca um registro pelo ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->query($sql, [$id]);
        
        if (!$stmt) return null;
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca todos os registros
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->query($sql);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca com filtros personalizados
     */
    public function find(string $where = '', array $params = [], string $order = '', int $limit = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        
        if (!empty($order)) {
            $sql .= " ORDER BY {$order}";
        }
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->query($sql, $params);
        
        if (!$stmt) return [];
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insere um novo registro
     */
    public function insert(array $data): int|false
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->query($sql, $values);
        
        if (!$stmt) return false;
        
        return (int)$this->connection->lastInsertId();
    }

    /**
     * Atualiza um registro
     */
    public function update(int $id, array $data): bool
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $values[] = $id;

        $setClause = implode(', ', array_map(fn($col) => "$col = ?", $columns));
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = ?";

        $stmt = $this->query($sql, $values);
        
        return $stmt !== false;
    }

    /**
     * Deleta um registro
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->query($sql, [$id]);
        
        return $stmt !== false;
    }

    /**
     * Conta registros
     */
    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        
        $stmt = $this->query($sql, $params);
        
        if (!$stmt) return 0;
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'] ?? 0;
    }
}
