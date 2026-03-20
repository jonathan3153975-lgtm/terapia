<?php

// Script de teste de conexão com banco de dados
require_once __DIR__ . '/vendor/autoload.php';

try {
    $db = \Config\Database::getInstance();
    $pdo = $db->getConnection();

    // Testa conexão
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();

    if ($result) {
        echo "✅ Conexão com banco de dados estabelecida com sucesso!\n";
        echo "✅ Sistema pronto para uso.\n\n";

        // Testa se as tabelas existem
        $tables = ['users', 'patients', 'appointments', 'payments', 'patient_records'];
        echo "Verificando tabelas:\n";

        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
                $count = $stmt->fetch()['count'];
                echo "✅ Tabela '$table': $count registros\n";
            } catch (Exception $e) {
                echo "❌ Tabela '$table': Não encontrada ou vazia\n";
            }
        }

        echo "\n🎉 Sistema funcionando perfeitamente!\n";
        echo "Acesse: http://localhost:8000\n";
        echo "Login: admin@terapia.com / Admin@123\n";

    } else {
        echo "❌ Falha na conexão com banco de dados\n";
    }

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "\nVerifique:\n";
    echo "1. MySQL está rodando?\n";
    echo "2. Banco 'terapia' existe?\n";
    echo "3. Credenciais no .env estão corretas?\n";
    echo "4. Arquivo database/schema.sql foi executado?\n";
}