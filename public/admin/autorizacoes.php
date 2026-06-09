<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Models\Autorizacao;
use MotoristaCheck\Models\Motorista;
use MotoristaCheck\Models\Aluno;
use MotoristaCheck\Models\Escola;
use MotoristaCheck\Models\LogAuditoria;

Auth::exigirPerfil(['admin', 'operador']);

$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        Autorizacao::create($_POST);
        LogAuditoria::registrar('AUTORIZACAO_CRIADA', 'Nova autorização motorista-aluno criada.');
        $sucesso = 'Autorização criada.';
    }
    if ($action === 'toggle') {
        $a = Autorizacao::findById((int)$_POST['id']);
        if ($a) {
            Autorizacao::update($a['id'], ['ativo' => $a['ativo'] ? 0 : 1]);
            $sucesso = 'Autorização ' . ($a['ativo'] ? 'desativada' : 'ativada') . '.';
        }
    }
    if ($action === 'delete') {
        Autorizacao::delete((int)$_POST['id']);
        $sucesso = 'Autorização excluída.';
    }
}

$autorizacoes = Autorizacao::findAll();
$motoristas = Motorista::findAll();
$alunos = Aluno::findAll();
$escolas = Escola::findAll();

$titulo = 'Autorizações';
$conteudo = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-check2-square"></i> Autorizações</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAuto"><i class="bi bi-plus-lg"></i> Nova Autorização</button>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable">
                <thead class="table-light">
                    <tr><th>Motorista</th><th>Aluno</th><th>Escola</th><th>Início</th><th>Fim</th><th>Ativo</th><th>Ações</th></tr>
                </thead>
                <tbody>';
foreach ($autorizacoes as $a) {
    $conteudo .= '<tr>
        <td>' . Security::h($a['motorista_nome']) . '</td>
        <td>' . Security::h($a['aluno_nome']) . '</td>
        <td>' . Security::h($a['escola_nome']) . '</td>
        <td>' . ($a['data_inicio'] ? date('d/m/Y', strtotime($a['data_inicio'])) : '-') . '</td>
        <td>' . ($a['data_fim'] ? date('d/m/Y', strtotime($a['data_fim'])) : '-') . '</td>
        <td>' . ($a['ativo'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>') . '</td>
        <td>
            <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="' . $a['id'] . '">
                <button class="btn btn-sm btn-outline-warning" title="Ativar/Desativar"><i class="bi bi-toggle-on"></i></button>
            </form>
            <form method="POST" style="display:inline" onsubmit="return confirm(\'Excluir?\')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' . $a['id'] . '">
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
        </td>
    </tr>';
}
$conteudo .= '
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAuto" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Autorização</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Motorista *</label>
                        <select name="motorista_id" class="form-select" required>';
foreach ($motoristas as $m) {
    $conteudo .= '<option value="' . $m['id'] . '">' . Security::h($m['nome']) . ' (' . Security::maskCpf($m['cpf']) . ')</option>';
}
$conteudo .= '          </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Aluno *</label>
                        <select name="aluno_id" class="form-select" required>';
foreach ($alunos as $a) {
    $conteudo .= '<option value="' . $a['id'] . '">' . Security::h($a['nome']) . ' - ' . Security::h($a['escola_nome'] ?? '') . '</option>';
}
$conteudo .= '          </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Escola</label>
                        <select name="escola_id" class="form-select" required>';
foreach ($escolas as $e) {
    $conteudo .= '<option value="' . $e['id'] . '">' . Security::h($e['nome']) . '</option>';
}
$conteudo .= '          </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Data Início</label>
                            <input type="date" name="data_inicio" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Data Fim</label>
                            <input type="date" name="data_fim" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </form>
    </div>
</div>
';

require_once __DIR__ . '/../../views/layouts/admin.php';
