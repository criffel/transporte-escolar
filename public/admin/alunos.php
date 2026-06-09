<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Models\Aluno;
use MotoristaCheck\Models\Escola;
use MotoristaCheck\Models\LogAuditoria;

Auth::exigirPerfil(['admin', 'operador']);

$erro = null;
$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        Aluno::create($_POST);
        LogAuditoria::registrar('ALUNO_CRIADO', "Aluno {$_POST['nome']} cadastrado.");
        $sucesso = 'Aluno cadastrado.';
    }
    if ($action === 'update') {
        Aluno::update((int)$_POST['id'], $_POST);
        $sucesso = 'Aluno atualizado.';
    }
    if ($action === 'delete') {
        Aluno::delete((int)$_POST['id']);
        $sucesso = 'Aluno excluído.';
    }
}

$alunos = Aluno::findAll();
$escolas = Escola::findAll();

$titulo = 'Alunos';
$conteudo = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-people"></i> Alunos</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAluno"><i class="bi bi-plus-lg"></i> Novo Aluno</button>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable">
                <thead class="table-light">
                    <tr><th>Nome</th><th>Escola</th><th>Série</th><th>Data Nasc.</th><th>Ativo</th><th>Ações</th></tr>
                </thead>
                <tbody>';
foreach ($alunos as $a) {
    $conteudo .= '<tr>
        <td>' . Security::h($a['nome']) . '</td>
        <td>' . Security::h($a['escola_nome']) . '</td>
        <td>' . Security::h($a['serie'] ?? '-') . '</td>
        <td>' . ($a['data_nascimento'] ? date('d/m/Y', strtotime($a['data_nascimento'])) : '-') . '</td>
        <td>' . ($a['ativo'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>') . '</td>
        <td>
            <button class="btn btn-sm btn-outline-primary" onclick="editarAluno(' . json_encode($a) . ')"><i class="bi bi-pencil"></i></button>
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

<div class="modal fade" id="modalAluno" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAlunoTitle">Novo Aluno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAlunoAction" value="create">
                    <input type="hidden" name="id" id="formAlunoId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome" id="fAlunoNome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Escola *</label>
                        <select name="escola_id" id="fAlunoEscola" class="form-select" required>';
foreach ($escolas as $e) {
    $conteudo .= '<option value="' . $e['id'] . '">' . Security::h($e['nome']) . '</option>';
}
$conteudo .= '      </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Data Nascimento</label>
                            <input type="date" name="data_nascimento" id="fAlunoData" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Série</label>
                            <input type="text" name="serie" id="fAlunoSerie" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Observação</label>
                        <textarea name="observacao" id="fAlunoObs" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="ativo" value="1" id="fAlunoAtivo" class="form-check-input" checked>
                        <label class="form-check-label">Ativo</label>
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

<script>
function editarAluno(d) {
    document.getElementById("modalAlunoTitle").textContent = "Editar Aluno";
    document.getElementById("formAlunoAction").value = "update";
    document.getElementById("formAlunoId").value = d.id;
    document.getElementById("fAlunoNome").value = d.nome;
    document.getElementById("fAlunoEscola").value = d.escola_id;
    document.getElementById("fAlunoData").value = d.data_nascimento || "";
    document.getElementById("fAlunoSerie").value = d.serie || "";
    document.getElementById("fAlunoObs").value = d.observacao || "";
    document.getElementById("fAlunoAtivo").checked = d.ativo == 1;
    new bootstrap.Modal(document.getElementById("modalAluno")).show();
}
</script>
';

require_once __DIR__ . '/../../views/layouts/admin.php';
