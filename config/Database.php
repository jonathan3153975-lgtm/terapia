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

    // Credenciais do banco (lidas do .env)
    private string $host;
    private string $database;
    private string $user;
    private string $password;
    private string $charset;

    private function __construct()
    {
        $this->loadEnv();
        $this->connect();
    }

    /**
     * Carrega variáveis de ambiente do arquivo .env
     */
    private function loadEnv(): void
    {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                $_ENV[$name] = $value;
            }
        }

        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->database = $_ENV['DB_DATABASE'] ?? 'terapia';
        $this->user = $_ENV['DB_USERNAME'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
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
                $this->host,
                $this->database,
                $this->charset
            );

            $this->connection = new PDO(
                $dsn,
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new \RuntimeException('Erro de conexão com banco de dados: ' . $e->getMessage());
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
