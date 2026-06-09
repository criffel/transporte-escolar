<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Helpers\Formatador;
use MotoristaCheck\Helpers\QrCodeHelper;
use MotoristaCheck\Helpers\QrCodeHelper as Qr;
use MotoristaCheck\Models\Aluno;
use MotoristaCheck\Models\LogAuditoria;

Auth::exigirPerfil(['admin', 'escola', 'operador']);

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $erro = 'Token não informado.';
} else {
    $dados = Qr::validarToken($token);

    if (!$dados) {
        $erro = 'QR Code inválido ou expirado. Solicite um novo QR Code ao motorista.';
        LogAuditoria::registrar('QR_INVALIDO', "Tentativa de validação com token inválido/expirado.");
    } else {
        $motoristaId = (int)$dados['motorista_id'];
        $alunos = Aluno::findByMotorista($motoristaId);
        LogAuditoria::registrar('QR_VALIDADO', "QR Code validado com sucesso.", [
            'motorista_id' => $motoristaId,
            'motorista'    => $dados['motorista_nome'],
            'inspetor'     => Auth::nome() ?? 'desconhecido',
        ]);
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validação do Motorista — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="/inspetor/escanear.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Escanear outro
                    </a>
                    <a href="/logout.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-box-arrow-left"></i> Sair
                    </a>
                </div>

                <?php if (isset($erro)): ?>
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body text-center p-5">
                            <i class="bi bi-x-octagon-fill text-danger" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-danger">QR Code Inválido</h4>
                            <p class="text-muted"><?= Security::h($erro) ?></p>
                            <a href="/inspetor/escanear.php" class="btn btn-primary mt-3">
                                <i class="bi bi-qr-code-scan"></i> Escanear Novamente
                            </a>
                        </div>
                    </div>
                <?php elseif (isset($dados)): ?>
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <?php $fotoUrl = $dados['foto'] ? '/public/assets/img/upload/' . $dados['foto'] : '/public/assets/img/default-avatar.png'; ?>
                                    <img src="<?= Security::h($fotoUrl) ?>"
                                         alt="Foto"
                                         class="rounded-circle border border-2 border-primary"
                                         style="width: 60px; height: 60px; object-fit: cover;"
                                         onerror="this.src='/public/assets/img/default-avatar.png'">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="fw-bold mb-0"><?= Security::h($dados['motorista_nome']) ?></h5>
                                    <small class="text-muted">
                                        <i class="bi bi-credit-card-2-front"></i>
                                        <?= Security::maskCpf($dados['cpf']) ?>
                                    </small>
                                </div>
                                <div>
                                    <?= Formatador::statusBadge($dados['motorista_status']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="info-box">
                                        <small class="text-muted">Transportadora</small>
                                        <p class="fw-bold mb-0"><?= Security::h($dados['transportadora_nome'] ?? '-') ?></p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box">
                                        <small class="text-muted">Telefone</small>
                                        <p class="fw-bold mb-0"><?= Security::h(Formatador::telefone($dados['telefone'] ?? '')) ?></p>
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

                            <h6 class="fw-bold text-muted mb-3">
                                <i class="bi bi-people"></i> Crianças Autorizadas
                                <span class="badge bg-primary rounded-pill"><?= count($alunos) ?></span>
                            </h6>

                            <form id="autorizacaoForm">
                                <input type="hidden" name="motorista_id" value="<?= $motoristaId ?>">
                                <input type="hidden" name="token" value="<?= Security::h($token) ?>">
                                <?= Security::csrfField() ?>

                                <?php if (empty($alunos)): ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        Nenhuma criança autorizada para este motorista.
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush mb-4">
                                        <?php foreach ($alunos as $aluno): ?>
                                        <label class="list-group-item d-flex align-items-center gap-3 border rounded-3 mb-2">
                                            <input type="checkbox" name="alunos[]" value="<?= $aluno['id'] ?>" class="form-check-input form-check-input-lg" checked>
                                            <div class="flex-grow-1">
                                                <p class="fw-bold mb-0"><?= Security::h($aluno['nome']) ?></p>
                                                <small class="text-muted">
                                                    <?= Security::h($aluno['escola_nome'] ?? '') ?>
                                                    <?= $aluno['serie'] ? ' · ' . Security::h($aluno['serie']) : '' ?>
                                                </small>
                                                <?php if (!empty($aluno['responsavel_nome'])): ?>
                                                    <br><small class="text-muted">
                                                        <i class="bi bi-person-heart"></i> Resp: <?= Security::h($aluno['responsavel_nome']) ?>
                                                        <?= $aluno['responsavel_telefone'] ? ' · ' . Formatador::telefone($aluno['responsavel_telefone']) : '' ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Decisão</label>
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-success btn-lg" onclick="autorizar()">
                                                <i class="bi bi-check-circle"></i> Autorizar Retirada
                                            </button>
                                            <button type="button" class="btn btn-danger btn-lg" onclick="recusar()">
                                                <i class="bi bi-x-circle"></i> Recusar Retirada
                                            </button>
                                        </div>
                                        <div class="mt-3" id="motivo-recusa" style="display:none;">
                                            <label class="form-label">Motivo da Recusa:</label>
                                            <textarea name="motivo_recusa" class="form-control" rows="2" placeholder="Informe o motivo da recusa"></textarea>
                                            <button type="button" class="btn btn-danger mt-2 w-100" onclick="confirmarRecusa()">Confirmar Recusa</button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </form>

                            <div id="resultado-acao" style="display:none;"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function autorizar() {
        const form = document.getElementById('autorizacaoForm');
        const formData = new FormData(form);
        formData.append('status', 'autorizada');

        document.getElementById('resultado-acao').innerHTML =
            '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Processando...</div>';
        document.getElementById('resultado-acao').style.display = 'block';

        fetch('/api/autorizar-retirada.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('resultado-acao').innerHTML =
                    '<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> ' +
                    data.message + '</div>';
                if (data.link_acompanhamento) {
                    document.getElementById('resultado-acao').innerHTML +=
                        '<div class="alert alert-info"><strong>Link de acompanhamento:</strong><br>' +
                        '<a href="' + data.link_acompanhamento + '" target="_blank" class="text-break">' +
                        data.link_acompanhamento + '</a></div>';
                }
                document.querySelectorAll('#autorizacaoForm button').forEach(b => b.disabled = true);
            } else {
                document.getElementById('resultado-acao').innerHTML =
                    '<div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> ' +
                    data.message + '</div>';
            }
        })
        .catch(err => {
            document.getElementById('resultado-acao').innerHTML =
                '<div class="alert alert-danger">Erro ao processar. Tente novamente.</div>';
        });
    }

    function recusar() {
        document.getElementById('motivo-recusa').style.display = 'block';
    }

    function confirmarRecusa() {
        const motivo = document.querySelector('[name="motivo_recusa"]').value.trim();
        if (!motivo) {
            alert('Informe o motivo da recusa.');
            return;
        }

        const form = document.getElementById('autorizacaoForm');
        const formData = new FormData(form);
        formData.append('status', 'recusada');
        formData.append('motivo_recusa', motivo);

        document.getElementById('resultado-acao').innerHTML =
            '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Processando...</div>';
        document.getElementById('resultado-acao').style.display = 'block';

        fetch('/api/autorizar-retirada.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('resultado-acao').innerHTML =
                    '<div class="alert alert-warning"><i class="bi bi-x-circle-fill"></i> ' +
                    data.message + '</div>';
                document.querySelectorAll('#autorizacaoForm button').forEach(b => b.disabled = true);
            } else {
                document.getElementById('resultado-acao').innerHTML =
                    '<div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> ' +
                    data.message + '</div>';
            }
        })
        .catch(err => {
            document.getElementById('resultado-acao').innerHTML =
                '<div class="alert alert-danger">Erro ao processar. Tente novamente.</div>';
        });
    }
    </script>
</body>
</html>
