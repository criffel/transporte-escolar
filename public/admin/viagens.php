<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Helpers\Formatador;
use MotoristaCheck\Models\Viagem;
use MotoristaCheck\Models\LogAuditoria;

Auth::exigirPerfil(['admin', 'operador']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'finalizar') {
    Viagem::finalizar((int)$_POST['id']);
    LogAuditoria::registrar('VIAGEM_FINALIZADA_ADMIN', "Viagem #{$_POST['id']} finalizada pelo admin.");
    $sucesso = 'Viagem finalizada.';
}

$viagens = Viagem::findAll();

$titulo = 'Viagens';
$conteudo = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-geo-alt"></i> Viagens</h4>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable">
                <thead class="table-light">
                    <tr><th>Motorista</th><th>Aluno</th><th>Status</th><th>Início</th><th>Fim</th><th>Duração</th><th>Token</th><th>Ações</th></tr>
                </thead>
                <tbody>';
foreach ($viagens as $v) {
    $badge = Formatador::statusBadge($v['status']);
    $duracao = Formatador::tempoDecorrido($v['inicio'], $v['fim']);
    $conteudo .= '<tr>
        <td>' . Security::h($v['motorista_nome']) . '</td>
        <td>' . Security::h($v['aluno_nome']) . '</td>
        <td>' . $badge . '</td>
        <td>' . Formatador::dataHora($v['inicio']) . '</td>
        <td>' . ($v['fim'] ? Formatador::dataHora($v['fim']) : '-') . '</td>
        <td>' . $duracao . '</td>
        <td><small class="text-muted">' . substr($v['token_acompanhamento'] ?? '', 0, 12) . '...</small></td>
        <td>
            ' . ($v['status'] === 'em_andamento' ? '
            <form method="POST" style="display:inline" onsubmit="return confirm(\'Finalizar viagem?\')">
                <input type="hidden" name="action" value="finalizar"><input type="hidden" name="id" value="' . $v['id'] . '">
                <button class="btn btn-sm btn-outline-success"><i class="bi bi-stop-circle"></i> Finalizar</button>
            </form>' : '') . '
            <a href="' . APP_URL . '/pai/acompanhar.php?token=' . urlencode($v['token_acompanhamento']) . '" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
        </td>
    </tr>';
}
$conteudo .= '
                </tbody>
            </table>
        </div>
    </div>
</div>
';

require_once __DIR__ . '/../../views/layouts/admin.php';
