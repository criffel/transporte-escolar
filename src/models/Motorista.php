<?php

namespace MotoristaCheck\Models;

use MotoristaCheck\Core\Database;
use PDO;

class Motorista
{
    public static function findAll(int $limit = 100, int $offset = 0): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT m.*, u.nome, u.email, u.login, u.ativo as usuario_ativo,
                   t.nome as transportadora_nome, v.placa, v.modelo, v.cor
            FROM motoristas m
            JOIN usuarios u ON u.id = m.usuario_id
            LEFT JOIN transportadoras t ON t.id = m.transportadora_id
            LEFT JOIN veiculos v ON v.motorista_id = m.id AND v.ativo = 1
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
            SELECT m.*, u.nome, u.email, u.login, u.ativo as usuario_ativo,
                   t.nome as transportadora_nome
            FROM motoristas m
            JOIN usuarios u ON u.id = m.usuario_id
            LEFT JOIN transportadoras t ON t.id = m.transportadora_id
            WHERE m.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function findByUsuarioId(int $usuarioId): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM motoristas WHERE usuario_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $usuarioId]);
        return $stmt->fetch();
    }

    public static function findByCpf(string $cpf): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT m.*, u.nome, u.email
            FROM motoristas m
            JOIN usuarios u ON u.id = m.usuario_id
            WHERE m.cpf = :cpf
            LIMIT 1
        ");
        $stmt->execute([':cpf' => $cpf]);
        return $stmt->fetch();
    }

    public static function validarIdentificacao(string $cpf, string $dataNascimento): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT m.*, u.nome, u.email, u.ativo as usuario_ativo,
                   t.nome as transportadora_nome,
                   v.placa, v.modelo, v.cor, v.ano
            FROM motoristas m
            JOIN usuarios u ON u.id = m.usuario_id
            LEFT JOIN transportadoras t ON t.id = m.transportadora_id
            LEFT JOIN veiculos v ON v.motorista_id = m.id AND v.ativo = 1
            WHERE m.cpf = :cpf
              AND m.data_nascimento = :data_nascimento
              AND m.ativo = 1
              AND u.ativo = 1
            LIMIT 1
        ");
        $stmt->execute([
            ':cpf'             => $cpf,
            ':data_nascimento' => $dataNascimento,
        ]);
        return $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO motoristas (usuario_id, transportadora_id, cpf, rg, data_nascimento, foto, telefone, status, observacao)
            VALUES (:usuario_id, :transportadora_id, :cpf, :rg, :data_nascimento, :foto, :telefone, :status, :observacao)
        ");
        $stmt->execute([
            ':usuario_id'        => $data['usuario_id'],
            ':transportadora_id' => $data['transportadora_id'] ?? null,
            ':cpf'               => $data['cpf'],
            ':rg'                => $data['rg'] ?? null,
            ':data_nascimento'   => $data['data_nascimento'],
            ':foto'              => $data['foto'] ?? null,
            ':telefone'          => $data['telefone'] ?? null,
            ':status'            => $data['status'] ?? 'pendente',
            ':observacao'        => $data['observacao'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::getInstance();
        $fields = [];
        $params = [':id' => $id];
        foreach (['transportadora_id', 'cpf', 'rg', 'data_nascimento', 'foto', 'telefone', 'status', 'observacao', 'ativo'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = :{$f}";
                $params[":{$f}"] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $sql = "UPDATE motoristas SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM motoristas WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function count(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM motoristas")->fetchColumn();
    }

    public static function countByStatus(string $status): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM motoristas WHERE status = :status");
        $stmt->execute([':status' => $status]);
        return (int)$stmt->fetchColumn();
    }

    public static function search(string $term): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT m.*, u.nome, u.email, t.nome as transportadora_nome
            FROM motoristas m
            JOIN usuarios u ON u.id = m.usuario_id
            LEFT JOIN transportadoras t ON t.id = m.transportadora_id
            WHERE u.nome LIKE :term OR m.cpf LIKE :term
            ORDER BY u.nome LIMIT 50
        ");
        $stmt->execute([':term' => "%{$term}%"]);
        return $stmt->fetchAll();
    }
}
