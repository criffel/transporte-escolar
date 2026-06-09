<?php

namespace MotoristaCheck\Models;

use MotoristaCheck\Core\Database;
use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use PDO;

class LogAuditoria
{
    public static function registrar(string $acao, string $descricao = '', ?array $dados = null): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO logs_auditoria (usuario_id, acao, descricao, ip, user_agent, dados)
            VALUES (:usuario_id, :acao, :descricao, :ip, :user_agent, :dados)
        ");
        $stmt->execute([
            ':usuario_id' => Auth::id(),
            ':acao'       => $acao,
            ':descricao'  => $descricao,
            ':ip'         => Security::getUserIp(),
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':dados'      => $dados ? json_encode($dados) : null,
        ]);
    }

    public static function findAll(int $limit = 100, int $offset = 0): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT l.*, u.nome as usuario_nome, u.login as usuario_login
            FROM logs_auditoria l
            LEFT JOIN usuarios u ON u.id = l.usuario_id
            ORDER BY l.data_hora DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findByAcao(string $acao, int $limit = 50): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT l.*, u.nome as usuario_nome
            FROM logs_auditoria l
            LEFT JOIN usuarios u ON u.id = l.usuario_id
            WHERE l.acao = :acao
            ORDER BY l.data_hora DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':acao', $acao, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM logs_auditoria")->fetchColumn();
    }

    public static function search(string $term): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT l.*, u.nome as usuario_nome
            FROM logs_auditoria l
            LEFT JOIN usuarios u ON u.id = l.usuario_id
            WHERE l.acao LIKE :term OR l.descricao LIKE :term OR u.nome LIKE :term
            ORDER BY l.data_hora DESC
            LIMIT 100
        ");
        $stmt->execute([':term' => "%{$term}%"]);
        return $stmt->fetchAll();
    }

    public static function limparAntigos(int $dias = 90): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM logs_auditoria WHERE data_hora < DATE_SUB(NOW(), INTERVAL :dias DAY)");
        $stmt->execute([':dias' => $dias]);
        return $stmt->rowCount();
    }
}
