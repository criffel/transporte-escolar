<?php

namespace MotoristaCheck\Middleware;

use MotoristaCheck\Core\Auth;

class AuthMiddleware
{
    public static function handle(string|array $perfis = []): void
    {
        Auth::exigirLogin();

        if (!empty($perfis)) {
            $perfis = is_array($perfis) ? $perfis : [$perfis];
            if (!in_array(Auth::perfil(), $perfis, true)) {
                http_response_code(403);
                require_once __DIR__ . '/../../views/errors/403.php';
                exit;
            }
        }
    }

    public static function guest(): void
    {
        if (Auth::check()) {
            header('Location: ' . APP_URL);
            exit;
        }
    }
}
