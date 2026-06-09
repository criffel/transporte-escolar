<?php

namespace MotoristaCheck\Helpers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use MotoristaCheck\Core\Database;
use MotoristaCheck\Core\Auth;

class QrCodeHelper
{
    public static function gerarToken(int $motoristaId): array
    {
        $pdo = Database::getInstance();

        $token = Security::generateToken(32);
        $expiraEm = date('Y-m-d H:i:s', time() + (QR_CODE_EXPIRY_MINUTES * 60));

        $stmt = $pdo->prepare("
            INSERT INTO qrcode_tokens (motorista_id, token, expira_em)
            VALUES (:motorista_id, :token, :expira_em)
        ");
        $stmt->execute([
            ':motorista_id' => $motoristaId,
            ':token'        => $token,
            ':expira_em'    => $expiraEm,
        ]);

        return [
            'id'        => (int)$pdo->lastInsertId(),
            'token'     => $token,
            'expira_em' => $expiraEm,
        ];
    }

    public static function validarToken(string $token): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT qt.*,
                   m.nome as motorista_nome, m.cpf, m.foto, m.status as motorista_status,
                   m.data_nascimento, m.telefone,
                   v.placa, v.modelo, v.cor,
                   t.nome as transportadora_nome
            FROM qrcode_tokens qt
            JOIN motoristas m ON m.id = qt.motorista_id
            LEFT JOIN veiculos v ON v.motorista_id = m.id AND v.ativo = 1
            LEFT JOIN transportadoras t ON t.id = m.transportadora_id
            WHERE qt.token = :token
            LIMIT 1
        ");
        $stmt->execute([':token' => $token]);
        $data = $stmt->fetch();

        if (!$data) return false;

        if ($data['usado'] == 1) return false;

        if (strtotime($data['expira_em']) < time()) return false;

        if ($data['motorista_status'] !== 'autorizado') return false;

        return $data;
    }

    public static function marcarUsado(string $token): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE qrcode_tokens SET usado = 1 WHERE token = :token");
        $stmt->execute([':token' => $token]);
    }

    public static function gerarQrCodePng(string $data): string
    {
        $options = new QROptions([
            'version'    => 5,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'   => QRCode::ECC_M,
            'scale'      => 5,
            'imageBase64' => true,
        ]);
        $qrcode = new QRCode($options);
        return $qrcode->render($data);
    }

    public static function gerarQrCodeSvg(string $data): string
    {
        $options = new QROptions([
            'version'    => 5,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel'   => QRCode::ECC_M,
            'scale'      => 5,
        ]);
        $qrcode = new QRCode($options);
        return $qrcode->render($data);
    }

    public static function gerarUrlCrachat(string $token): string
    {
        return APP_URL . '/inspetor/validar.php?token=' . urlencode($token);
    }

    public static function limparTokensExpirados(): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM qrcode_tokens WHERE expira_em < NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
