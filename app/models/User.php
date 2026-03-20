<?php

namespace App\Models;

use Classes\Model;

class User extends Model
{
    protected string $table = 'users';

    /**
     * Busca usuário por email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        $stmt = $this->query($sql, [$email]);
        
        if (!$stmt) return null;
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Busca usuário por CPF
     */
    public function findByCPF(string $cpf): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE cpf = ? LIMIT 1";
        $stmt = $this->query($sql, [$cpf]);
        
        if (!$stmt) return null;
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Cria um novo usuário
     */
    public function createUser(array $userData): int|false
    {
        return $this->insert($userData);
    }

    /**
     * Lista todos os pacientes
     */
    public function listPatients(): array
    {
        return $this->find("role = 'patient'", [], 'name ASC');
    }

    /**
     * Lista administradores
     */
    public function listAdmins(): array
    {
        return $this->find("role = 'admin'", [], 'name ASC');
    }
}
