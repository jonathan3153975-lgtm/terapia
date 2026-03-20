<?php

namespace Config;

use PDO;
use PDOException;

/**
 * Classe responsável pela conexão com banco de dados
 * Padrão: Singleton
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    // Credenciais do banco
    private const HOST = 'terapia.mysql.dbaas.com.br';
    private const DATABASE = 'terapia';
    private const USER = 'terapia';
    private const PASSWORD = 'Jonathan315@@';
    private const CHARSET = 'utf8mb4';

    private function __construct()
    {
        $this->connect();
    }

    /**
     * Obtém instância única do banco de dados
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Conecta ao banco de dados
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::HOST,
                self::DATABASE,
                self::CHARSET
            );

            $this->connection = new PDO(
                $dsn,
                self::USER,
                self::PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die('Erro de conexão: ' . $e->getMessage());
        }
    }

    /**
     * Retorna conexão PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Executa uma query
     */
    public function query(string $sql, array $params = []): \PDOStatement|false
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Impede clonagem
     */
    private function __clone()
    {
    }

    /**
     * Impede desserialização
     */
    public function __wakeup()
    {
    }
}
