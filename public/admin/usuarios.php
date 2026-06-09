<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Models\Usuario;
use MotoristaCheck\Models\LogAuditoria;

Auth::exigirPerfil(['admin']);

$sucesso = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        if (Usuario::findByLogin($_POST['login'])) {
            $erro = 'Login já existe.';
        } else {
            Usuario::create($_POST);
            LogAuditoria::registrar('USUARIO_CRIADO', "Usuário {$_POST['login']} criado.");
            $sucesso = 'Usuário criado.';
        }
    }
    if ($action === 'update') {
        Usuario::update((int)$_POST['id'], $_POST);
        $sucesso = 'Usuário atualizado.';
    }
    if ($action === 'delete') {
        if ((int)$_POST['id'] === Auth::id()) {
            $erro = 'Você não pode excluir seu próprio usuário.';
        } else {
            Usuario::delete((int)$_POST['id']);
            $sucesso = 'Usuário excluído.';
        }
    }
}

$usuarios = Usuario::findAll();

$titulo = 'Usuários';
$conteudo = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-key"></i> Usuários do Sistema</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario"><i class="bi bi-plus-lg"></i> Novo Usuário</button>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable">
                <thead class="table-light">
                    <tr><th>Nome</th><th>Login</th><th>Email</th><th>Perfil</th><th>Ativo</th><th>Último Acesso</th><th>Ações</th></tr>
                </thead>
                <tbody>';
foreach ($usuarios as $u) {
    $conteudo .= '<tr>
        <td>' . Security::h($u['nome']) . '</td>
        <td><strong>' . Security::h($u['login']) . '</strong></td>
        <td>' . Security::h($u['email'] ?? '-') . '</td>
        <td><span class="badge bg-info">' . Security::h($u['perfil_nome']) . '</span></td>
        <td>' . ($u['ativo'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>') . '</td>
        <td class="small text-muted">' . ($u['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($u['ultimo_acesso'])) : 'Nunca') . '</td>
        <td>
            <button class="btn btn-sm btn-outline-primary" onclick="editarUsuario(' . json_encode($u) . ')"><i class="bi bi-pencil"></i></button>
            <form method="POST" style="display:inline" onsubmit="return confirm(\'Excluir usuário?\')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' . $u['id'] . '">
                <button class="btn btn-sm btn-outline-danger" ' . ($u['id'] === Auth::id() ? 'disabled' : '') . '><i class="bi bi-trash"></i></button>
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

<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUserTitle">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formUserAction" value="create">
                    <input type="hidden" name="id" id="formUserId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome" id="fUserNome" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Login *</label>
                            <input type="text" name="login" id="fUserLogin" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Senha *</label>
                            <input type="password" name="senha" id="fUserSenha" class="form-control" minlength="6">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" id="fUserEmail" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perfil *</label>
                        <select name="perfil_id" id="fUserPerfil" class="form-select" required>
                            <option value="1">Admin</option>
                            <option value="2">Escola/Inspetor</option>
                            <option value="3">Motorista</option>
                            <option value="4">Responsável</option>
                            <option value="5">Operador</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="ativo" value="1" id="fUserAtivo" class="form-check-input" checked>
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
function editarUsuario(d) {
    document.getElementById("modalUserTitle").textContent = "Editar Usuário";
    document.getElementById("formUserAction").value = "update";
    document.getElementById("formUserId").value = d.id;
    document.getElementById("fUserNome").value = d.nome;
    document.getElementById("fUserLogin").value = d.login;
    document.getElementById("fUserSenha").value = "";
    document.getElementById("fUserSenha").required = false;
    document.getElementById("fUserEmail").value = d.email || "";
    document.getElementById("fUserPerfil").value = d.perfil_id;
    document.getElementById("fUserAtivo").checked = d.ativo == 1;
    new bootstrap.Modal(document.getElementById("modalUsuario")).show();
}
</script>
';

require_once __DIR__ . '/../../views/layouts/admin.php';
