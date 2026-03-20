<?php
/**
 * Script para criar usuário admin vanderleia@terapia.com
 * Execute: php database/create-vanderleia.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Config\Database;
use Helpers\Utils;

$db = Database::getInstance();

$email    = 'vanderleia@terapia.com';
$name     = 'Vanderleia';
$password = Utils::hashPassword('Vanderleia2025');
$role     = 'admin';
$status   = 'active';

// Verifica se já existe
$stmt = $db->query("SELECT id FROM users WHERE email = ?", [$email]);
$existing = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : null;

if ($existing) {
    // Atualiza senha e role
    $db->query(
        "UPDATE users SET name = ?, password = ?, role = ?, status = ? WHERE email = ?",
        [$name, $password, $role, $status, $email]
    );
    echo "Usuário '{$email}' atualizado com sucesso.\n";
} else {
    $db->query(
        "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
        [$name, $email, $password, $role, $status]
    );
    echo "Usuário '{$email}' criado com sucesso.\n";
}

echo "Email : {$email}\n";
echo "Senha : Vanderleia2025\n";
echo "Papel : admin\n";
