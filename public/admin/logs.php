<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Helpers\Formatador;
use MotoristaCheck\Models\LogAuditoria;

Auth::exigirPerfil(['admin']);

$search = $_GET['search'] ?? '';
if ($search) {
    $logs = LogAuditoria::search($search);
} else {
    $logs = LogAuditoria::findAll(200);
}

$titulo = 'Logs de Auditoria';
$conteudo = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-journal-text"></i> Logs de Auditoria</h4>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar por ação, descrição ou usuário..." value="' . Security::h($search) . '">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar</button>
                <a href="logs.php" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>
        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Data/Hora</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Descrição</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>';
foreach ($logs as $l) {
    $conteudo .= '<tr>
        <td class="small">' . Formatador::dataHora($l['data_hora']) . '</td>
        <td>' . Security::h($l['usuario_nome'] ?? 'Sistema') . '</td>
        <td><span class="badge bg-secondary">' . Security::h($l['acao']) . '</span></td>
        <td class="small">' . Security::h($l['descricao'] ?? '-') . '</td>
        <td class="small text-muted">' . Security::h($l['ip'] ?? '-') . '</td>
    </tr>';
}
if (empty($logs)) {
    $conteudo .= '<tr><td colspan="5" class="text-center text-muted py-3">Nenhum registro encontrado.</td></tr>';
}
$conteudo .= '
                </tbody>
            </table>
        </div>
        <small class="text-muted">Total: ' . count($logs) . ' registro(s)</small>
    </div>
</div>
';

require_once __DIR__ . '/../../views/layouts/admin.php';
