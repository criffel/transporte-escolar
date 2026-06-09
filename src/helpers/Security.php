<?php

namespace MotoristaCheck\Helpers;

class Security
{
    public static function h(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }

    public static function sanitize(string $input): string
    {
        $input = trim($input);
        $input = stripslashes($input);
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }

    public static function onlyDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    public static function maskCpf(string $cpf): string
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11) return $cpf;
        return substr($cpf, 0, 3) . '.***.***-' . substr($cpf, -2);
    }

    public static function maskCnpj(string $cnpj): string
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        if (strlen($cnpj) !== 14) return $cnpj;
        return substr($cnpj, 0, 2) . '.***.***/****-' . substr($cnpj, -2);
    }

    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) return $email;
        $name = $parts[0];
        $masked = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
        return $masked . '@' . $parts[1];
    }

    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function validateCsrfToken(string $token): bool
    {
        if (empty($_SESSION['_csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }

    public static function generateToken(int $length = 48): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function limitSession(string $key, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        $now = time();
        if (!isset($_SESSION['_rate_limit'][$key])) {
            $_SESSION['_rate_limit'][$key] = [];
        }
        $_SESSION['_rate_limit'][$key] = array_filter(
            $_SESSION['_rate_limit'][$key],
            fn($t) => ($now - $t) < $timeWindow
        );
        if (count($_SESSION['_rate_limit'][$key]) >= $maxAttempts) {
            return false;
        }
        $_SESSION['_rate_limit'][$key][] = $now;
        return true;
    }

    public static function getUserIp(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        return trim($ip);
    }
}
