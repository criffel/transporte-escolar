<?php

namespace MotoristaCheck\Core;

use PDO;

class Auth
{
    public static function tentarLogin(string $login, string $senha): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT u.*, p.nome as perfil_nome
            FROM usuarios u
            JOIN perfis p ON p.id = u.perfil_id
            WHERE u.login = :login AND u.ativo = 1
            LIMIT 1
        ");
        $stmt->execute([':login' => $login]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            return false;
        }

        $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = :id");
        $stmt->execute([':id' => $usuario['id']]);

        $_SESSION['usuario'] = [
            'id'          => (int)$usuario['id'],
            'perfil_id'   => (int)$usuario['perfil_id'],
            'perfil_nome' => $usuario['perfil_nome'],
            'nome'        => $usuario['nome'],
            'email'       => $usuario['email'],
            'login'       => $usuario['login'],
        ];

        session_regenerate_id(true);

        return $_SESSION['usuario'];
    }

    public static function usuarioLogado(): array|false
    {
        return $_SESSION['usuario'] ?? false;
    }

    public static function check(): bool
    {
        return isset($_SESSION['usuario']);
    }

    public static function perfil(): ?string
    {
        return $_SESSION['usuario']['perfil_nome'] ?? null;
    }

    public static function is(string $perfil): bool
    {
        return self::perfil() === $perfil;
    }

    public static function isAdmin(): bool
    {
        return self::is('admin');
    }

    public static function isEscola(): bool
    {
        return self::is('escola');
    }

    public static function isMotorista(): bool
    {
        return self::is('motorista');
    }

    public static function isResponsavel(): bool
    {
        return self::is('responsavel');
    }

    public static function isOperador(): bool
    {
        return self::is('operador');
    }

    public static function id(): ?int
    {
        return $_SESSION['usuario']['id'] ?? null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function exigirLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
    }

    public static function exigirPerfil(string|array $perfis): void
    {
        self::exigirLogin();
        $perfis = is_array($perfis) ? $perfis : [$perfis];
        if (!in_array(self::perfil(), $perfis)) {
            http_response_code(403);
            die('Acesso não autorizado para este perfil.');
        }
    }
}
