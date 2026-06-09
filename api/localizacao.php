<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/session.php';

use MotoristaCheck\Core\Database;
use MotoristaCheck\Models\LogAuditoria;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$token = $_POST['token'] ?? '';
$latitude  = (float)($_POST['latitude'] ?? 0);
$longitude = (float)($_POST['longitude'] ?? 0);
$velocidade = (float)($_POST['velocidade'] ?? 0);

if (empty($token) || empty($latitude) || empty($longitude)) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
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

$stmt = $pdo->prepare("
    INSERT INTO localizacoes_viagem (viagem_id, latitude, longitude, velocidade, data_hora)
    VALUES (:viagem_id, :latitude, :longitude, :velocidade, NOW())
");
$stmt->execute([
    ':viagem_id'  => $viagem['id'],
    ':latitude'   => $latitude,
    ':longitude'  => $longitude,
    ':velocidade' => $velocidade ?: null,
]);

echo json_encode(['success' => true, 'message' => 'Localização atualizada.']);
