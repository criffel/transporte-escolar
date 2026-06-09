<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Models\Escola;
use MotoristaCheck\Models\LogAuditoria;

Auth::exigirPerfil(['admin', 'operador']);

$sucesso = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        Escola::create($_POST);
        LogAuditoria::registrar('ESCOLA_CRIADA', "Escola {$_POST['nome']} cadastrada.");
        $sucesso = 'Escola cadastrada.';
    }
    if ($action === 'update') {
        Escola::update((int)$_POST['id'], $_POST);
        $sucesso = 'Escola atualizada.';
    }
    if ($action === 'delete') {
        Escola::delete((int)$_POST['id']);
        $sucesso = 'Escola excluída.';
    }
}

$escolas = Escola::findAllWithCount();

$titulo = 'Escolas';
$conteudo = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-building"></i> Escolas</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEscola"><i class="bi bi-plus-lg"></i> Nova Escola</button>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable">
                <thead class="table-light">
                    <tr><th>Nome</th><th>Telefone</th><th>Contato</th><th>Alunos</th><th>Ativo</th><th>Ações</th></tr>
                </thead>
                <tbody>';
foreach ($escolas as $e) {
    $conteudo .= '<tr>
        <td>' . Security::h($e['nome']) . '</td>
        <td>' . Security::h($e['telefone'] ?? '-') . '</td>
        <td>' . Security::h($e['contato'] ?? '-') . '</td>
        <td><span class="badge bg-primary">' . $e['total_alunos'] . '</span></td>
        <td>' . ($e['ativo'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>') . '</td>
        <td>
            <button class="btn btn-sm btn-outline-primary" onclick="editarEscola(' . json_encode($e) . ')"><i class="bi bi-pencil"></i></button>
            <form method="POST" style="display:inline" onsubmit="return confirm(\'Excluir?\')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' . $e['id'] . '">
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

<div class="modal fade" id="modalEscola" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEscolaTitle">Nova Escola</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formEscolaAction" value="create">
                    <input type="hidden" name="id" id="formEscolaId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome" id="fEscolaNome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Endereço</label>
                        <textarea name="endereco" id="fEscolaEndereco" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" id="fEscolaTelefone" class="form-control tel-mask">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contato</label>
                            <input type="text" name="contato" id="fEscolaContato" class="form-control">
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

<script>
function editarEscola(d) {
    document.getElementById("modalEscolaTitle").textContent = "Editar Escola";
    document.getElementById("formEscolaAction").value = "update";
    document.getElementById("formEscolaId").value = d.id;
    document.getElementById("fEscolaNome").value = d.nome;
    document.getElementById("fEscolaEndereco").value = d.endereco || "";
    document.getElementById("fEscolaTelefone").value = d.telefone || "";
    document.getElementById("fEscolaContato").value = d.contato || "";
    new bootstrap.Modal(document.getElementById("modalEscola")).show();
}
</script>
';

require_once __DIR__ . '/../../views/layouts/admin.php';
