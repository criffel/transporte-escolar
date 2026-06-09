<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Helpers\Formatador;
use MotoristaCheck\Models\Viagem;

$token = $_GET['token'] ?? '';

if (empty($token)) {
    http_response_code(404);
    die('<div class="alert alert-danger">Link inválido.</div>');
}

$viagem = Viagem::findByToken($token);

if (!$viagem) {
    http_response_code(404);
    $invalido = true;
}

$finalizada = $viagem && $viagem['status'] === 'finalizada';

$fotoMotorista = $viagem['motorista_foto'] ? '/public/assets/img/upload/' . $viagem['motorista_foto'] : '/public/assets/img/default-avatar.png';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acompanhamento — Transporte Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <style>
    #mapa { height: 350px; border-radius: 12px; }
    .status-indicator { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
    .status-em_andamento { background-color: #0d6efd; animation: pulse 2s infinite; }
    .status-finalizada { background-color: #198754; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <?php if (isset($invalido)): ?>
                    <div class="card shadow-sm border-0 rounded-4 text-center p-5">
                        <i class="bi bi-shield-exclamation text-danger" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Link Inválido ou Expirado</h4>
                        <p class="text-muted">Este link de acompanhamento não é mais válido.</p>
                    </div>
                <?php else: ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="ms-2">
                            <h5 class="fw-bold mb-0"><?= APP_NAME ?></h5>
                            <small class="text-muted">Acompanhamento em Tempo Real</small>
                        </div>
                        <div class="ms-auto">
                            <span class="badge bg-<?= $viagem['status'] === 'em_andamento' ? 'primary' : 'success' ?> fs-6">
                                <span class="status-indicator status-<?= $viagem['status'] ?> me-1"></span>
                                <?= $viagem['status'] === 'em_andamento' ? 'Em andamento' : 'Finalizada' ?>
                            </span>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4 mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <img src="<?= Security::h($fotoMotorista) ?>"
                                     alt="Motorista"
                                     class="rounded-circle border border-2 border-primary shadow-sm"
                                     style="width: 70px; height: 70px; object-fit: cover;"
                                     onerror="this.src='/public/assets/img/default-avatar.png'">
                                <div class="ms-3 flex-grow-1">
                                    <h5 class="fw-bold mb-1"><?= Security::h($viagem['motorista_nome']) ?></h5>
                                    <small class="text-muted">
                                        <i class="bi bi-truck"></i>
                                        <?= Security::h($viagem['placa'] ?? '') ?> —
                                        <?= Security::h($viagem['modelo'] ?? '') ?>
                                        (<?= Security::h($viagem['cor'] ?? '') ?>)
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4 mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-person-standing text-warning" style="font-size: 1.5rem;"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="fw-bold mb-1"><?= Security::h($viagem['aluno_nome']) ?></h5>
                                    <small class="text-muted">
                                        <i class="bi bi-building"></i> <?= Security::h($viagem['escola_nome'] ?? '') ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4 mb-3">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-muted mb-3"><i class="bi bi-geo-alt"></i> Localização</h6>
                            <div id="mapa"></div>
                            <div class="row g-2 mt-3 small text-muted">
                                <div class="col-6">
                                    <i class="bi bi-clock"></i> Saída: <?= Formatador::dataHora($viagem['retirada_data_hora']) ?>
                                </div>
                                <div class="col-6 text-end" id="ultima-atualizacao">
                                    <i class="bi bi-arrow-repeat"></i> Aguardando...
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4 mb-3">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-muted mb-3"><i class="bi bi-info-circle"></i> Detalhes da Viagem</h6>
                            <div class="row g-2 small">
                                <div class="col-6">
                                    <span class="text-muted">Transportadora</span>
                                    <p class="fw-bold mb-0"><?= Security::h($viagem['transportadora_nome'] ?? '-') ?></p>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Contato Transportadora</span>
                                    <p class="fw-bold mb-0"><?= Formatador::telefone($viagem['transportadora_telefone'] ?? '') ?></p>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Escola</span>
                                    <p class="fw-bold mb-0"><?= Security::h($viagem['escola_nome'] ?? '-') ?></p>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Telefone Escola</span>
                                    <p class="fw-bold mb-0"><?= Formatador::telefone($viagem['escola_telefone'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="tel:<?= Security::h($viagem['escola_telefone'] ?? '') ?>" class="btn btn-outline-danger w-100 mb-3">
                        <i class="bi bi-telephone-fill"></i> Emergência — Ligar para Escola
                    </a>

                    <?php if (!$finalizada): ?>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <i class="bi bi-shield-lock"></i>
                            Link seguro · Atualizado em tempo real
                        </small>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (isset($viagem) && $viagem && !$finalizada): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    const viagemId = <?= $viagem['id'] ?>;
    const viagemToken = '<?= Security::h($token) ?>';

    const map = L.map('mapa').setView([-23.5505, -46.6333], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);

    const motoristaIcon = L.divIcon({
        html: '<div style="background:#0d6efd;color:white;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);"><i class="bi bi-truck" style="font-size:16px;"></i></div>',
        iconSize: [32, 32],
        iconAnchor: [16, 16],
        className: ''
    });

    const marker = L.marker([-23.5505, -46.6333], { icon: motoristaIcon }).addTo(map);
    marker.bindPopup('<strong>Motorista</strong><br><small>Última localização conhecida</small>');

    function atualizarLocalizacao() {
        fetch('/api/localizacao-pai.php?token=' + encodeURIComponent(viagemToken))
            .then(r => r.json())
            .then(data => {
                if (data.latitude && data.longitude) {
                    const lat = parseFloat(data.latitude);
                    const lng = parseFloat(data.longitude);
                    marker.setLatLng([lat, lng]);
                    map.setView([lat, lng], map.getZoom());
                    document.getElementById('ultima-atualizacao').innerHTML =
                        '<i class="bi bi-arrow-repeat"></i> ' + data.data_hora;
                }
                if (data.finalizada) {
                    location.reload();
                }
            })
            .catch(() => {});
    }

    setInterval(atualizarLocalizacao, 5000);
    setTimeout(atualizarLocalizacao, 1000);
    </script>
    <?php elseif (isset($viagem) && $viagem && $finalizada): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    const map = L.map('mapa').setView([-23.5505, -46.6333], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);

    fetch('/api/localizacao-pai.php?token=<?= Security::h($token) ?>&historico=1')
        .then(r => r.json())
        .then(data => {
            if (data.historico && data.historico.length > 0) {
                const coords = data.historico.map(p => [parseFloat(p.latitude), parseFloat(p.longitude)]);
                const polyline = L.polyline(coords, { color: '#0d6efd', weight: 3 }).addTo(map);
                map.fitBounds(polyline.getBounds(), { padding: [40, 40] });

                const finalIcon = L.divIcon({
                    html: '<div style="background:#198754;color:white;border-radius:50%;width:24px;height:24px;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                    className: ''
                });
                L.marker(coords[coords.length - 1], { icon: finalIcon }).addTo(map)
                    .bindPopup('<strong>Destino final</strong>');
            }
        });
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
