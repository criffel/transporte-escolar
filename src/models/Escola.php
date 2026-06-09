<?php

namespace MotoristaCheck\Models;

use MotoristaCheck\Core\Database;
use PDO;

class Escola
{
    public static function findAll(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query("SELECT * FROM escolas WHERE ativo = 1 ORDER BY nome")->fetchAll();
    }

    public static function findAllWithCount(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT e.*, (SELECT COUNT(*) FROM alunos a WHERE a.escola_id = e.id AND a.ativo = 1) as total_alunos
            FROM escolas e
            ORDER BY e.nome
        ");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM escolas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO escolas (nome, endereco, telefone, contato)
            VALUES (:nome, :endereco, :telefone, :contato)
        ");
        $stmt->execute([
            ':nome'     => $data['nome'],
            ':endereco' => $data['endereco'] ?? null,
            ':telefone' => $data['telefone'] ?? null,
            ':contato'  => $data['contato'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::getInstance();
        $fields = [];
        $params = [':id' => $id];
        foreach (['nome', 'endereco', 'telefone', 'contato', 'ativo'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = :{$f}";
                $params[":{$f}"] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $sql = "UPDATE escolas SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM escolas WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function count(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM escolas")->fetchColumn();
    }
}
