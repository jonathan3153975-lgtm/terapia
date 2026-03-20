<?php

namespace Helpers;

/**
 * Classe com funções utilitárias gerais
 */
class Utils
{
    /**
     * Sanitiza string de entrada
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valida email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Gera hash de senha
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verifica hash de senha
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Gera token único
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Formata valor monetário
     */
    public static function formatMoney(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    /**
     * Converte valor monetário para float
     */
    public static function parseMoneyToFloat(string $value): float
    {
        $value = str_replace(['R$', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);
        return (float)$value;
    }

    /**
     * Formata data
     */
    public static function formatDate(string $date, string $format = 'd/m/Y'): string
    {
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }

    /**
     * Converte data de formato PT-BR para banco
     */
    public static function parseDateToDB(string $date): string
    {
        $parts = explode('/', $date);
        if (count($parts) === 3) {
            return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
        return $date;
    }

    /**
     * Obtém diferença em dias
     */
    public static function dateDiff(string $date1, string $date2): int
    {
        $timestamp1 = strtotime($date1);
        $timestamp2 = strtotime($date2);
        return abs(($timestamp2 - $timestamp1) / 86400);
    }

    /**
     * Calcula idade a partir da data de nascimento
     */
    public static function calculateAge(string $birthDate): int
    {
        $today = new \DateTime();
        $birth = new \DateTime($birthDate);
        $diff = $today->diff($birth);
        return $diff->y;
    }

    /**
     * Gera slug a partir de string
     */
    public static function slug(string $string): string
    {
        $string = strtolower(trim($string));
        $string = preg_replace('/[^a-z0-9]+/', '-', $string);
        return trim($string, '-');
    }

    /**
     * Trunca string
     */
    public static function truncate(string $string, int $length = 100, string $append = '...'): string
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        return substr($string, 0, $length) . $append;
    }
}
