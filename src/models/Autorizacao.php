<?php

namespace MotoristaCheck\Models;

use MotoristaCheck\Core\Database;
use PDO;

class Autorizacao
{
    public static function findAll(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT ama.*,
                   mu.nome as motorista_nome,
                   a.nome as aluno_nome,
                   e.nome as escola_nome
            FROM autorizacoes_motorista_aluno ama
            JOIN motoristas m ON m.id = ama.motorista_id
            JOIN usuarios mu ON mu.id = m.usuario_id
            JOIN alunos a ON a.id = ama.aluno_id
            JOIN escolas e ON e.id = ama.escola_id
            ORDER BY mu.nome, a.nome
        ");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM autorizacoes_motorista_aluno WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO autorizacoes_motorista_aluno (motorista_id, aluno_id, escola_id, data_inicio, data_fim)
            VALUES (:motorista_id, :aluno_id, :escola_id, :data_inicio, :data_fim)
            ON DUPLICATE KEY UPDATE
                escola_id = VALUES(escola_id),
                ativo = 1,
                data_inicio = VALUES(data_inicio),
                data_fim = VALUES(data_fim)
        ");
        $stmt->execute([
            ':motorista_id' => $data['motorista_id'],
            ':aluno_id'     => $data['aluno_id'],
            ':escola_id'    => $data['escola_id'],
            ':data_inicio'  => $data['data_inicio'] ?? null,
            ':data_fim'     => $data['data_fim'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::getInstance();
        $fields = [];
        $params = [':id' => $id];
        foreach (['motorista_id', 'aluno_id', 'escola_id', 'ativo', 'data_inicio', 'data_fim'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = :{$f}";
                $params[":{$f}"] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $sql = "UPDATE autorizacoes_motorista_aluno SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM autorizacoes_motorista_aluno WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function count(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM autorizacoes_motorista_aluno WHERE ativo = 1")->fetchColumn();
    }

    public static function isAutorizado(int $motoristaId, int $alunoId): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM autorizacoes_motorista_aluno
            WHERE motorista_id = :mid AND aluno_id = :aid AND ativo = 1
              AND (data_inicio IS NULL OR data_inicio <= CURDATE())
              AND (data_fim IS NULL OR data_fim >= CURDATE())
        ");
        $stmt->execute([':mid' => $motoristaId, ':aid' => $alunoId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
