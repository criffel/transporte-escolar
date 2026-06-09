<?php

namespace MotoristaCheck\Models;

use MotoristaCheck\Core\Database;
use MotoristaCheck\Helpers\Security;
use PDO;

class Viagem
{
    public static function findAll(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT v.*,
                   mu.nome as motorista_nome,
                   a.nome as aluno_nome,
                   r.status as retirada_status
            FROM viagens v
            JOIN motoristas m ON m.id = v.motorista_id
            JOIN usuarios mu ON mu.id = m.usuario_id
            JOIN alunos a ON a.id = v.aluno_id
            JOIN retiradas r ON r.id = v.retirada_id
            ORDER BY v.inicio DESC
        ");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT v.*,
                   mu.nome as motorista_nome,
                   a.nome as aluno_nome,
                   r.status as retirada_status
            FROM viagens v
            JOIN motoristas m ON m.id = v.motorista_id
            JOIN usuarios mu ON mu.id = m.usuario_id
            JOIN alunos a ON a.id = v.aluno_id
            JOIN retiradas r ON r.id = v.retirada_id
            WHERE v.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function findByToken(string $token): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT v.*,
                   mu.nome as motorista_nome, mu.email as motorista_email,
                   m.foto as motorista_foto, m.cpf as motorista_cpf, m.telefone as motorista_telefone,
                   vv.placa, vv.modelo, vv.cor,
                   a.nome as aluno_nome, a.foto as aluno_foto,
                   e.nome as escola_nome, e.endereco as escola_endereco, e.telefone as escola_telefone,
                   t.nome as transportadora_nome, t.telefone as transportadora_telefone,
                   r.data_hora as retirada_data_hora
            FROM viagens v
            JOIN motoristas m ON m.id = v.motorista_id
            JOIN usuarios mu ON mu.id = m.usuario_id
            LEFT JOIN veiculos vv ON vv.motorista_id = m.id AND vv.ativo = 1
            JOIN alunos a ON a.id = v.aluno_id
            JOIN escolas e ON e.id = a.escola_id
            LEFT JOIN transportadoras t ON t.id = m.transportadora_id
            JOIN retiradas r ON r.id = v.retirada_id
            WHERE v.token_acompanhamento = :token
            LIMIT 1
        ");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    public static function iniciar(int $retiradaId, int $motoristaId, int $alunoId): array
    {
        $pdo = Database::getInstance();
        $token = Security::generateToken(24);

        $stmt = $pdo->prepare("
            INSERT INTO viagens (retirada_id, motorista_id, aluno_id, token_acompanhamento)
            VALUES (:retirada_id, :motorista_id, :aluno_id, :token)
        ");
        $stmt->execute([
            ':retirada_id'  => $retiradaId,
            ':motorista_id' => $motoristaId,
            ':aluno_id'     => $alunoId,
            ':token'        => $token,
        ]);

        return [
            'id'      => (int)$pdo->lastInsertId(),
            'token'   => $token,
        ];
    }

    public static function finalizar(int $id): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            UPDATE viagens SET status = 'finalizada', fim = NOW()
            WHERE id = :id AND status = 'em_andamento'
        ");
        $stmt->execute([':id' => $id]);
    }

    public static function cancelar(int $id): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            UPDATE viagens SET status = 'cancelada', fim = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
    }

    public static function emAndamento(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT v.*, mu.nome as motorista_nome, a.nome as aluno_nome
            FROM viagens v
            JOIN motoristas m ON m.id = v.motorista_id
            JOIN usuarios mu ON mu.id = m.usuario_id
            JOIN alunos a ON a.id = v.aluno_id
            WHERE v.status = 'em_andamento'
            ORDER BY v.inicio DESC
        ");
        return $stmt->fetchAll();
    }

    public static function count(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM viagens")->fetchColumn();
    }

    public static function countEmAndamento(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("SELECT COUNT(*) FROM viagens WHERE status = 'em_andamento'")->fetchColumn();
    }

    public static function countFinalizadasHoje(): int
    {
        $pdo = Database::getInstance();
        return (int)$pdo->query("
            SELECT COUNT(*) FROM viagens
            WHERE status = 'finalizada' AND DATE(inicio) = CURDATE()
        ")->fetchColumn();
    }
}
