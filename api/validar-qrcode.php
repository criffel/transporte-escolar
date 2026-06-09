<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/session.php';

use MotoristaCheck\Helpers\QrCodeHelper;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Models\LogAuditoria;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$token = $_POST['token'] ?? '';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Token não informado.']);
    exit;
}

$dados = QrCodeHelper::validarToken($token);

if (!$dados) {
    LogAuditoria::registrar('QR_VALIDACAO_FALHA', 'Tentativa de validação de QR Code falhou.', ['token' => substr($token, 0, 8) . '...']);
    echo json_encode(['success' => false, 'message' => 'QR Code inválido, expirado ou já utilizado.']);
    exit;
}

LogAuditoria::registrar('QR_VALIDADO', 'QR Code validado com sucesso.', [
    'motorista_id' => $dados['motorista_id'],
    'motorista'    => $dados['motorista_nome'],
]);

echo json_encode([
    'success' => true,
    'message' => 'QR Code válido.',
    'dados'   => [
        'motorista_id' => $dados['motorista_id'],
        'nome'         => $dados['motorista_nome'],
        'cpf'          => Security::maskCpf($dados['cpf']),
        'foto'         => $dados['foto'],
        'status'       => $dados['motorista_status'],
        'transportadora' => $dados['transportadora_nome'],
        'placa'        => $dados['placa'],
        'modelo'       => $dados['modelo'],
        'cor'          => $dados['cor'],
        'expira_em'    => $dados['expira_em'],
    ],
]);
