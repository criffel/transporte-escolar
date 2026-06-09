<?php

namespace MotoristaCheck\Models;

use MotoristaCheck\Core\Database;
use PDO;

class Retirada
{
    public static function findAll(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT r.*,
                   mu.nome as motorista_nome,
                   a.nome as aluno_nome,
                   e.nome as escola_nome,
                   i.nome as inspetor_nome
            FROM retiradas r
            JOIN motoristas m ON m.id = r.motorista_id
            JOIN usuarios mu ON mu.id = m.usuario_id
            JOIN alunos a ON a.id = r.aluno_id
            JOIN escolas e ON e.id = r.escola_id
            LEFT JOIN usuarios i ON i.id = r.inspetor_id
            ORDER BY r.data_hora DESC
        ");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT r.*,
                   mu.nome as motorista_nome,
                   a.nome as aluno_nome,
                   e.nome as escola_nome,
                   i.nome as inspetor_nome
            FROM retiradas r
            JOIN motoristas m ON m.id = r.motorista_id
            JOIN usuarios mu ON mu.id = m.usuario_id
            JOIN alunos a ON a.id = r.aluno_id
            JOIN escolas e ON e.id = r.escola_id
            LEFT JOIN usuarios i ON i.id = r.inspetor_id
            WHERE r.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO retiradas (motorista_id, aluno_id, escola_id, inspetor_id, status, motivo_recusa,
                                   localizacao_lat, localizacao_lng, data_hora)
            VALUES (:motorista_id, :aluno_id, :escola_id, :inspetor_id, :status, :motivo_recusa,
                    :localizacao_lat, :localizacao_lng, NOW())
        ");
        $stmt->execute([
            ':motorista_id'    => $data['motorista_id'],
            ':aluno_id'        => $data['aluno_id'],
            ':escola_id'       => $data['escola_id'],
            ':inspetor_id'     => $data['inspetor_id'] ?? null,
            ':status'          => $data['status'],
            ':motivo_recusa'   => $data['motivo_recusa'] ?? null,
            ':localizacao_lat' => $data['localizacao_lat'] ?? null,
            ':localizacao_lng' => $data['localizacao_lng'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function count(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM retiradas")->fetchColumn();
    }

    public static function countByStatus(string $status): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM retiradas WHERE status = :status");
        $stmt->execute([':status' => $status]);
        return (int)$stmt->fetchColumn();
    }

    public static function countToday(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM retiradas WHERE DATE(data_hora) = CURDATE()")->fetchColumn();
    }

    public static function countTodayByStatus(string $status): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM retiradas
            WHERE DATE(data_hora) = CURDATE() AND status = :status
        ");
        $stmt->execute([':status' => $status]);
        return (int)$stmt->fetchColumn();
    }

    public static function hoje(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT r.*,
                   mu.nome as motorista_nome, m.foto as motorista_foto,
                   a.nome as aluno_nome,
                   e.nome as escola_nome,
                   i.nome as inspetor_nome
            FROM retiradas r
            JOIN motoristas m ON m.id = r.motorista_id
            JOIN usuarios mu ON mu.id = m.usuario_id
            JOIN alunos a ON a.id = r.aluno_id
            JOIN escolas e ON e.id = r.escola_id
            LEFT JOIN usuarios i ON i.id = r.inspetor_id
            WHERE DATE(r.data_hora) = CURDATE()
            ORDER BY r.data_hora DESC
        ");
        return $stmt->fetchAll();
    }
}
