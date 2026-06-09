<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Core\Database;
use MotoristaCheck\Models\LogAuditoria;
use MotoristaCheck\Models\Viagem;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$token = $_POST['token'] ?? '';
$viagemId = (int)($_POST['viagem_id'] ?? 0);

if (empty($token) && empty($viagemId)) {
    echo json_encode(['success' => false, 'message' => 'Identificador da viagem não informado.']);
    exit;
}

if ($viagemId) {
    Viagem::finalizar($viagemId);
    LogAuditoria::registrar('VIAGEM_FINALIZADA', "Viagem #{$viagemId} finalizada.");
    echo json_encode(['success' => true, 'message' => 'Viagem finalizada com sucesso.']);
    exit;
}

$pdo = Database::getInstance();
$stmt = $pdo->prepare("SELECT id FROM viagens WHERE token_acompanhamento = :token AND status = 'em_andamento' LIMIT 1");
$stmt->execute([':token' => $token]);
$viagem = $stmt->fetch();

if (!$viagem) {
    echo json_encode(['success' => false, 'message' => 'Viagem não encontrada ou já finalizada.']);
    exit;
}

Viagem::finalizar($viagem['id']);
LogAuditoria::registrar('VIAGEM_FINALIZADA', "Viagem #{$viagem['id']} finalizada via token.");
echo json_encode(['success' => true, 'message' => 'Viagem finalizada com sucesso.']);
