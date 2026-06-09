<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Models\Transportadora;
use MotoristaCheck\Models\LogAuditoria;

Auth::exigirPerfil(['admin', 'operador']);

$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        Transportadora::create($_POST);
        LogAuditoria::registrar('TRANSPORTADORA_CRIADA', "Transportadora {$_POST['nome']} cadastrada.");
        $sucesso = 'Transportadora cadastrada.';
    }
    if ($action === 'update') {
        Transportadora::update((int)$_POST['id'], $_POST);
        $sucesso = 'Transportadora atualizada.';
    }
    if ($action === 'delete') {
        Transportadora::delete((int)$_POST['id']);
        $sucesso = 'Transportadora excluída.';
    }
}

$transportadoras = Transportadora::findAll();

$titulo = 'Transportadoras';
$conteudo = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-building-gear"></i> Transportadoras</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTransportadora"><i class="bi bi-plus-lg"></i> Nova Transportadora</button>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable">
                <thead class="table-light">
                    <tr><th>Nome</th><th>CNPJ</th><th>Telefone</th><th>Contato</th><th>Ativo</th><th>Ações</th></tr>
                </thead>
                <tbody>';
foreach ($transportadoras as $t) {
    $conteudo .= '<tr>
        <td>' . Security::h($t['nome']) . '</td>
        <td>' . Security::h($t['cnpj']) . '</td>
        <td>' . Security::h($t['telefone'] ?? '-') . '</td>
        <td>' . Security::h($t['contato'] ?? '-') . '</td>
        <td>' . ($t['ativo'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>') . '</td>
        <td>
            <button class="btn btn-sm btn-outline-primary" onclick="editarTransportadora(' . json_encode($t) . ')"><i class="bi bi-pencil"></i></button>
            <form method="POST" style="display:inline" onsubmit="return confirm(\'Excluir?\')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' . $t['id'] . '">
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

<div class="modal fade" id="modalTransportadora" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTranspTitle">Nova Transportadora</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formTranspAction" value="create">
                    <input type="hidden" name="id" id="formTranspId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome" id="fTranspNome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">CNPJ *</label>
                        <input type="text" name="cnpj" id="fTranspCnpj" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" id="fTranspTelefone" class="form-control tel-mask">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contato</label>
                            <input type="text" name="contato" id="fTranspContato" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Endereço</label>
                        <textarea name="endereco" id="fTranspEndereco" class="form-control" rows="2"></textarea>
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
function editarTransportadora(d) {
    document.getElementById("modalTranspTitle").textContent = "Editar Transportadora";
    document.getElementById("formTranspAction").value = "update";
    document.getElementById("formTranspId").value = d.id;
    document.getElementById("fTranspNome").value = d.nome;
    document.getElementById("fTranspCnpj").value = d.cnpj;
    document.getElementById("fTranspTelefone").value = d.telefone || "";
    document.getElementById("fTranspContato").value = d.contato || "";
    document.getElementById("fTranspEndereco").value = d.endereco || "";
    new bootstrap.Modal(document.getElementById("modalTransportadora")).show();
}
</script>
';

require_once __DIR__ . '/../../views/layouts/admin.php';
