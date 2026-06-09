<?php

namespace MotoristaCheck\Models;

use MotoristaCheck\Core\Database;
use PDO;

class Responsavel
{
    public static function findAll(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT r.*, u.nome, u.email, u.login
            FROM responsaveis r
            JOIN usuarios u ON u.id = r.usuario_id
            ORDER BY r.nome
        ");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT r.*, u.nome, u.email, u.login
            FROM responsaveis r
            JOIN usuarios u ON u.id = r.usuario_id
            WHERE r.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function findByUsuarioId(int $usuarioId): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM responsaveis WHERE usuario_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $usuarioId]);
        return $stmt->fetch();
    }

    public static function findByAluno(int $alunoId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT r.*, u.nome, u.email, ra.principal
            FROM responsavel_aluno ra
            JOIN responsaveis r ON r.id = ra.responsavel_id
            JOIN usuarios u ON u.id = r.usuario_id
            WHERE ra.aluno_id = :aid
        ");
        $stmt->execute([':aid' => $alunoId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO responsaveis (usuario_id, nome, cpf, telefone, parentesco)
            VALUES (:usuario_id, :nome, :cpf, :telefone, :parentesco)
        ");
        $stmt->execute([
            ':usuario_id' => $data['usuario_id'],
            ':nome'       => $data['nome'],
            ':cpf'        => $data['cpf'],
            ':telefone'   => $data['telefone'] ?? null,
            ':parentesco' => $data['parentesco'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::getInstance();
        $fields = [];
        $params = [':id' => $id];
        foreach (['nome', 'cpf', 'telefone', 'parentesco', 'ativo'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = :{$f}";
                $params[":{$f}"] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $sql = "UPDATE responsaveis SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM responsaveis WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function count(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM responsaveis")->fetchColumn();
    }

    public static function vincularAluno(int $responsavelId, int $alunoId, bool $principal = false): void
    {
        $pdo = Database::getInstance();
        if ($principal) {
            $pdo->prepare("UPDATE responsavel_aluno SET principal = 0 WHERE responsavel_id = :rid")
                ->execute([':rid' => $responsavelId]);
        }
        $stmt = $pdo->prepare("
            INSERT INTO responsavel_aluno (responsavel_id, aluno_id, principal)
            VALUES (:rid, :aid, :principal)
            ON DUPLICATE KEY UPDATE principal = :principal2
        ");
        $stmt->execute([
            ':rid'       => $responsavelId,
            ':aid'       => $alunoId,
            ':principal' => $principal ? 1 : 0,
            ':principal2'=> $principal ? 1 : 0,
        ]);
    }

    public static function desvincularAluno(int $responsavelId, int $alunoId): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM responsavel_aluno WHERE responsavel_id = :rid AND aluno_id = :aid");
        $stmt->execute([':rid' => $responsavelId, ':aid' => $alunoId]);
    }
}
