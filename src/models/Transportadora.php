<?php

namespace MotoristaCheck\Models;

use MotoristaCheck\Core\Database;

class Transportadora
{
    public static function findAll(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query("SELECT * FROM transportadoras WHERE ativo = 1 ORDER BY nome")->fetchAll();
    }

    public static function findById(int $id): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM transportadoras WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO transportadoras (nome, cnpj, telefone, contato, endereco)
            VALUES (:nome, :cnpj, :telefone, :contato, :endereco)
        ");
        $stmt->execute([
            ':nome'     => $data['nome'],
            ':cnpj'     => $data['cnpj'],
            ':telefone' => $data['telefone'] ?? null,
            ':contato'  => $data['contato'] ?? null,
            ':endereco' => $data['endereco'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::getInstance();
        $fields = [];
        $params = [':id' => $id];
        foreach (['nome', 'cnpj', 'telefone', 'contato', 'endereco', 'ativo'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = :{$f}";
                $params[":{$f}"] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $sql = "UPDATE transportadoras SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM transportadoras WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function count(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM transportadoras")->fetchColumn();
    }
}
