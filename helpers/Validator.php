<?php

namespace Helpers;

/**
 * Validador de dados - CPF, Telefone, CEP
 */
class Validator
{
    /**
     * Valida CPF
     */
    public static function validateCPF(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Calcula primeiro dígito
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int)$cpf[9] !== $digit1) {
            return false;
        }

        // Calcula segundo dígito
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int)$cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int)$cpf[10] !== $digit2) {
            return false;
        }

        return true;
    }

    /**
     * Formata CPF com máscara
     */
    public static function formatCPF(string $cpf): string
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    /**
     * Remove máscara do CPF
     */
    public static function removeCPFMask(string $cpf): string
    {
        return preg_replace('/\D/', '', $cpf);
    }

    /**
     * Valida telefone
     */
    public static function validatePhone(string $phone): bool
    {
        $phone = preg_replace('/\D/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 11;
    }

    /**
     * Formata telefone
     */
    public static function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
        } elseif (strlen($phone) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
        }

        return $phone;
    }

    /**
     * Remove máscara do telefone
     */
    public static function removePhoneMask(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }

    /**
     * Valida CEP
     */
    public static function validateCEP(string $cep): bool
    {
        $cep = preg_replace('/\D/', '', $cep);
        return strlen($cep) === 8 && is_numeric($cep);
    }

    /**
     * Formata CEP
     */
    public static function formatCEP(string $cep): string
    {
        $cep = preg_replace('/\D/', '', $cep);
        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
    }

    /**
     * Remove máscara do CEP
     */
    public static function removeCEPMask(string $cep): string
    {
        return preg_replace('/\D/', '', $cep);
    }

    /**
     * Valida senha
     */
    public static function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'A senha deve ter pelo menos 8 caracteres';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Deve conter pelo menos uma letra maiúscula';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Deve conter pelo menos uma letra minúscula';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Deve conter pelo menos um número';
        }

        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Deve conter pelo menos um caractere especial';
        }

        return $errors;
    }

    /**
     * Valida data
     */
    public static function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
