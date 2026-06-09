<?php

namespace MotoristaCheck\Models;

use MotoristaCheck\Core\Database;
use PDO;

class Usuario
{
    public static function findAll(int $limit = 100, int $offset = 0): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT u.*, p.nome as perfil_nome
            FROM usuarios u
            JOIN perfis p ON p.id = u.perfil_id
            ORDER BY u.nome
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findById(int $id): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT u.*, p.nome as perfil_nome
            FROM usuarios u
            JOIN perfis p ON p.id = u.perfil_id
            WHERE u.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function findByLogin(string $login): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE login = :login LIMIT 1");
        $stmt->execute([':login' => $login]);
        return $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (perfil_id, nome, email, login, senha, ativo)
            VALUES (:perfil_id, :nome, :email, :login, :senha, :ativo)
        ");
        $stmt->execute([
            ':perfil_id' => $data['perfil_id'],
            ':nome'      => $data['nome'],
            ':email'     => $data['email'],
            ':login'     => $data['login'],
            ':senha'     => $data['senha'],
            ':ativo'     => $data['ativo'] ?? 1,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::getInstance();
        $fields = [];
        $params = [':id' => $id];
        foreach (['perfil_id', 'nome', 'email', 'login', 'ativo'] as $f) {
            if (isset($data[$f])) {
                $fields[] = "{$f} = :{$f}";
                $params[":{$f}"] = $data[$f];
            }
        }
        if (!empty($data['senha'])) {
            $fields[] = "senha = :senha";
            $params[':senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }
        if (empty($fields)) return;
        $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function count(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    }

    public static function search(string $term): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT u.*, p.nome as perfil_nome
            FROM usuarios u
            JOIN perfis p ON p.id = u.perfil_id
            WHERE u.nome LIKE :term OR u.email LIKE :term OR u.login LIKE :term
            ORDER BY u.nome LIMIT 50
        ");
        $stmt->execute([':term' => "%{$term}%"]);
        return $stmt->fetchAll();
    }
}
