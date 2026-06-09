<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Helpers\Formatador;
use MotoristaCheck\Helpers\QrCodeHelper;
use MotoristaCheck\Models\Aluno;
use MotoristaCheck\Models\LogAuditoria;

$dados = $_SESSION['motorista_identificado'] ?? null;

if (!$dados) {
    header('Location: /motorista/identificar.php');
    exit;
}

if ($dados['status'] !== 'autorizado') {
    $statusBloqueado = true;
}

$tokenInfo = QrCodeHelper::gerarToken((int)$dados['id']);
$qrData = QrCodeHelper::gerarUrlCrachat($tokenInfo['token']);
$qrImage = QrCodeHelper::gerarQrCodePng($qrData);

$alunos = Aluno::findByMotorista((int)$dados['id']);

LogAuditoria::registrar('CRACHA_VISUALIZADO', "Motorista {$dados['nome']} visualizou crachá digital.");

$fotoUrl = $dados['foto'] ? '/public/assets/img/upload/' . $dados['foto'] : '/public/assets/img/default-avatar.png';
$expiraEm = date('d/m/Y H:i:s', strtotime($tokenInfo['expira_em']));
$refreshEm = QR_CODE_EXPIRY_MINUTES * 60 * 1000;

$statusClass = match ($dados['status']) {
    'autorizado' => 'success',
    'bloqueado'  => 'danger',
    'pendente'   => 'warning',
    default      => 'secondary',
};
$statusIcon = match ($dados['status']) {
    'autorizado' => 'check-circle-fill',
    'bloqueado'  => 'x-circle-fill',
    'pendente'   => 'exclamation-circle-fill',
    default      => 'question-circle-fill',
};

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crachá Digital — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="text-center mb-3">
                    <a href="/logout.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-box-arrow-left"></i> Sair
                    </a>
                </div>

                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="cracha-header bg-gradient-<?= $statusClass ?> p-4 text-white text-center position-relative">
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-<?= $statusClass ?> bg-opacity-75 fs-6">
                                <i class="bi bi-<?= $statusIcon ?>"></i>
                                <?= ucfirst($dados['status']) ?>
                            </span>
                        </div>
                        <div class="mt-2">
                            <img src="<?= Security::h($fotoUrl) ?>"
                                 alt="Foto do motorista"
                                 class="rounded-circle border border-4 border-white shadow"
                                 style="width: 120px; height: 120px; object-fit: cover;"
                                 onerror="this.src='/public/assets/img/default-avatar.png'">
                        </div>
                        <h4 class="mt-3 fw-bold"><?= Security::h($dados['nome']) ?></h4>
                        <p class="mb-0 small opacity-75">
                            <i class="bi bi-credit-card-2-front"></i>
                            <?= Security::maskCpf($dados['cpf']) ?>
                        </p>
                    </div>

                    <div class="card-body p-4">
                        <?php if (isset($statusBloqueado)): ?>
                            <div class="alert alert-danger text-center">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                Seu acesso está <strong><?= $dados['status'] ?></strong>.
                                Procure a transportadora ou administração.
                            </div>
                        <?php endif; ?>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="info-box">
                                    <small class="text-muted">Transportadora</small>
                                    <p class="fw-bold mb-0"><?= Security::h($dados['transportadora_nome'] ?? 'Não vinculada') ?></p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box">
                                    <small class="text-muted">Telefone</small>
                                    <p class="fw-bold mb-0"><?= $dados['telefone'] ? Formatador::telefone($dados['telefone']) : '-' ?></p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="info-box">
                                    <small class="text-muted">Placa</small>
                                    <p class="fw-bold mb-0"><?= Security::h($dados['placa'] ?? '-') ?></p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="info-box">
                                    <small class="text-muted">Modelo</small>
                                    <p class="fw-bold mb-0"><?= Security::h($dados['modelo'] ?? '-') ?></p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="info-box">
                                    <small class="text-muted">Cor</small>
                                    <p class="fw-bold mb-0"><?= Security::h($dados['cor'] ?? '-') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mb-3">
                            <h6 class="text-muted">QR Code de Validação</h6>
                            <p class="small text-muted">Válido até: <span id="qr-expira"><?= $expiraEm ?></span></p>
                            <div class="d-inline-block p-3 bg-white rounded-3 shadow-sm border" id="qr-container">
                                <img src="<?= $qrImage ?>" alt="QR Code" class="img-fluid" style="max-width: 220px;" id="qr-image">
                            </div>
                            <p class="small text-muted mt-2">
                                <i class="bi bi-arrow-repeat"></i>
                                Atualiza automaticamente a cada <?= QR_CODE_EXPIRY_MINUTES ?> minutos
                            </p>
                        </div>

                        <?php if (!empty($alunos)): ?>
                        <div class="mt-4">
                            <h6 class="fw-bold text-muted mb-3">
                                <i class="bi bi-people"></i> Crianças Autorizadas
                            </h6>
                            <div class="list-group list-group-flush">
                                <?php foreach ($alunos as $aluno): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <p class="fw-bold mb-0"><?= Security::h($aluno['nome']) ?></p>
                                        <small class="text-muted">
                                            <?= Security::h($aluno['escola_nome'] ?? '') ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><?= $aluno['serie'] ?? '-' ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    setInterval(function() {
        fetch('/motorista/cracha.php?refresh=1')
            .then(r => r.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newQr = doc.getElementById('qr-image');
                const newExpira = doc.getElementById('qr-expira');
                if (newQr && newExpira) {
                    document.getElementById('qr-image').src = newQr.src;
                    document.getElementById('qr-expira').textContent = newExpira.textContent;
                }
            })
            .catch(() => {});
    }, <?= $refreshEm ?>);

    setTimeout(function() {
        location.reload();
    }, <?= $refreshEm ?> + 5000);
    </script>
</body>
</html>
