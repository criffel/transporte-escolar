<?php

namespace MotoristaCheck\Models;

use MotoristaCheck\Core\Database;
use PDO;

class Veiculo
{
    public static function findAll(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT v.*, u.nome as motorista_nome
            FROM veiculos v
            JOIN motoristas m ON m.id = v.motorista_id
            JOIN usuarios u ON u.id = m.usuario_id
            ORDER BY v.placa
        ");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM veiculos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function findByMotorista(int $motoristaId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM veiculos WHERE motorista_id = :mid AND ativo = 1");
        $stmt->execute([':mid' => $motoristaId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO veiculos (motorista_id, placa, modelo, cor, ano, capacidade)
            VALUES (:motorista_id, :placa, :modelo, :cor, :ano, :capacidade)
        ");
        $stmt->execute([
            ':motorista_id' => $data['motorista_id'],
            ':placa'        => $data['placa'],
            ':modelo'       => $data['modelo'],
            ':cor'          => $data['cor'],
            ':ano'          => $data['ano'] ?? null,
            ':capacidade'   => $data['capacidade'] ?? 1,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::getInstance();
        $fields = [];
        $params = [':id' => $id];
        foreach (['motorista_id', 'placa', 'modelo', 'cor', 'ano', 'capacidade', 'ativo'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = :{$f}";
                $params[":{$f}"] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $sql = "UPDATE veiculos SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM veiculos WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function count(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM veiculos")->fetchColumn();
    }
}
