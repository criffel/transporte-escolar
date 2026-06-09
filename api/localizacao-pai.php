<?php

require_once __DIR__ . '/../config/app.php';

use MotoristaCheck\Core\Database;
use MotoristaCheck\Helpers\Formatador;

header('Content-Type: application/json; charset=utf-8');

header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');

$token = $_GET['token'] ?? '';
$historico = (int)($_GET['historico'] ?? 0);

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Token não informado.']);
    exit;
}

$pdo = Database::getInstance();

$stmt = $pdo->prepare("
    SELECT id, status FROM viagens WHERE token_acompanhamento = :token LIMIT 1
");
$stmt->execute([':token' => $token]);
$viagem = $stmt->fetch();

if (!$viagem) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Viagem não encontrada.']);
    exit;
}

if ($historico || $viagem['status'] === 'finalizada') {
    $stmt = $pdo->prepare("
        SELECT latitude, longitude, velocidade, data_hora
        FROM localizacoes_viagem
        WHERE viagem_id = :vid
        ORDER BY data_hora ASC
    ");
    $stmt->execute([':vid' => $viagem['id']]);
    $pontos = $stmt->fetchAll();

    echo json_encode([
        'success'   => true,
        'finalizada' => true,
        'historico'  => $pontos,
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT latitude, longitude, velocidade, data_hora
    FROM localizacoes_viagem
    WHERE viagem_id = :vid
    ORDER BY data_hora DESC
    LIMIT 1
");
$stmt->execute([':vid' => $viagem['id']]);
$ultimaPosicao = $stmt->fetch();

if ($ultimaPosicao) {
    echo json_encode([
        'success'    => true,
        'finalizada' => false,
        'latitude'   => $ultimaPosicao['latitude'],
        'longitude'  => $ultimaPosicao['longitude'],
        'velocidade' => $ultimaPosicao['velocidade'],
        'data_hora'  => Formatador::dataHora($ultimaPosicao['data_hora']),
    ]);
} else {
    echo json_encode([
        'success'    => true,
        'finalizada' => false,
        'latitude'   => null,
        'longitude'  => null,
        'velocidade' => null,
        'data_hora'  => null,
        'message'    => 'Aguardando localização do motorista...',
    ]);
}
