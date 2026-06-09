<?php

namespace MotoristaCheck\Models;

use MotoristaCheck\Core\Database;
use PDO;

class Aluno
{
    public static function findAll(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT a.*, e.nome as escola_nome
            FROM alunos a
            JOIN escolas e ON e.id = a.escola_id
            ORDER BY a.nome
        ");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT a.*, e.nome as escola_nome
            FROM alunos a
            JOIN escolas e ON e.id = a.escola_id
            WHERE a.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function findByEscola(int $escolaId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM alunos WHERE escola_id = :eid AND ativo = 1 ORDER BY nome");
        $stmt->execute([':eid' => $escolaId]);
        return $stmt->fetchAll();
    }

    public static function findByMotorista(int $motoristaId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT a.*, e.nome as escola_nome,
                   ra.responsavel_id, r.nome as responsavel_nome, r.telefone as responsavel_telefone
            FROM autorizacoes_motorista_aluno ama
            JOIN alunos a ON a.id = ama.aluno_id AND a.ativo = 1
            JOIN escolas e ON e.id = ama.escola_id
            LEFT JOIN responsavel_aluno ra ON ra.aluno_id = a.id AND ra.principal = 1
            LEFT JOIN responsaveis r ON r.id = ra.responsavel_id
            WHERE ama.motorista_id = :mid
              AND ama.ativo = 1
              AND (ama.data_inicio IS NULL OR ama.data_inicio <= CURDATE())
              AND (ama.data_fim IS NULL OR ama.data_fim >= CURDATE())
            ORDER BY a.nome
        ");
        $stmt->execute([':mid' => $motoristaId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO alunos (escola_id, nome, data_nascimento, serie, foto, observacao)
            VALUES (:escola_id, :nome, :data_nascimento, :serie, :foto, :observacao)
        ");
        $stmt->execute([
            ':escola_id'       => $data['escola_id'],
            ':nome'            => $data['nome'],
            ':data_nascimento' => $data['data_nascimento'] ?? null,
            ':serie'           => $data['serie'] ?? null,
            ':foto'            => $data['foto'] ?? null,
            ':observacao'      => $data['observacao'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::getInstance();
        $fields = [];
        $params = [':id' => $id];
        foreach (['escola_id', 'nome', 'data_nascimento', 'serie', 'foto', 'observacao', 'ativo'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = :{$f}";
                $params[":{$f}"] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $sql = "UPDATE alunos SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM alunos WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function count(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM alunos")->fetchColumn();
    }

    public static function countByEscola(int $escolaId): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM alunos WHERE escola_id = :eid AND ativo = 1");
        $stmt->execute([':eid' => $escolaId]);
        return (int)$stmt->fetchColumn();
    }
}
