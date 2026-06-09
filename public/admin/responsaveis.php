<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Models\Responsavel;
use MotoristaCheck\Models\Usuario;
use MotoristaCheck\Models\Aluno;
use MotoristaCheck\Models\LogAuditoria;

Auth::exigirPerfil(['admin', 'operador']);

$sucesso = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $usuarioId = Usuario::create([
            'perfil_id' => 4,
            'nome'      => $_POST['nome'],
            'email'     => $_POST['email'] ?: "resp_{$_POST['cpf']}@motorista.local",
            'login'     => $_POST['login'],
            'senha'     => $_POST['senha'],
            'ativo'     => 1,
        ]);
        $respId = Responsavel::create([
            'usuario_id' => $usuarioId,
            'nome'       => $_POST['nome'],
            'cpf'        => preg_replace('/\D/', '', $_POST['cpf']),
            'telefone'   => $_POST['telefone'] ?? null,
            'parentesco' => $_POST['parentesco'] ?? null,
        ]);
        if (!empty($_POST['alunos'])) {
            foreach ($_POST['alunos'] as $aid) {
                Responsavel::vincularAluno($respId, (int)$aid);
            }
        }
        LogAuditoria::registrar('RESPONSAVEL_CRIADO', "Responsável {$_POST['nome']} cadastrado.");
        $sucesso = 'Responsável cadastrado.';
    }
    if ($action === 'delete') {
        $r = Responsavel::findById((int)$_POST['id']);
        if ($r) {
            Usuario::delete($r['usuario_id']);
            $sucesso = 'Responsável excluído.';
        }
    }
    if ($action === 'vincular') {
        $alunoId = (int)($_POST['aluno_id'] ?? 0);
        $respId = (int)($_POST['responsavel_id'] ?? 0);
        if ($alunoId && $respId) {
            Responsavel::vincularAluno($respId, $alunoId, !empty($_POST['principal']));
            $sucesso = 'Vínculo realizado.';
        }
    }
}

$responsaveis = Responsavel::findAll();
$alunos = Aluno::findAll();

$titulo = 'Responsáveis';
$conteudo = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-heart"></i> Responsáveis</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalResp"><i class="bi bi-plus-lg"></i> Novo Responsável</button>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable">
                <thead class="table-light">
                    <tr><th>Nome</th><th>CPF</th><th>Telefone</th><th>Parentesco</th><th>Ações</th></tr>
                </thead>
                <tbody>';
foreach ($responsaveis as $r) {
    $conteudo .= '<tr>
        <td>' . Security::h($r['nome']) . '</td>
        <td>' . Security::maskCpf($r['cpf']) . '</td>
        <td>' . Security::h($r['telefone'] ?? '-') . '</td>
        <td>' . Security::h($r['parentesco'] ?? '-') . '</td>
        <td>
            <button class="btn btn-sm btn-outline-info" onclick="vincularAluno(' . $r['id'] . ', \'' . Security::h($r['nome']) . '\')"><i class="bi bi-link-45deg"></i></button>
            <form method="POST" style="display:inline" onsubmit="return confirm(\'Excluir?\')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' . $r['id'] . '">
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

<div class="modal fade" id="modalResp" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Responsável</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">CPF *</label>
                            <input type="text" name="cpf" class="form-control cpf-mask" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control tel-mask">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Parentesco</label>
                            <select name="parentesco" class="form-select">
                                <option value="Pai">Pai</option>
                                <option value="Mãe">Mãe</option>
                                <option value="Avô/Avó">Avô/Avó</option>
                                <option value="Tio/Tia">Tio/Tia</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Login *</label>
                            <input type="text" name="login" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Senha *</label>
                            <input type="password" name="senha" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Vincular Alunos</label>
                        <select name="alunos[]" class="form-select" multiple>';
foreach ($alunos as $a) {
    $conteudo .= '<option value="' . $a['id'] . '">' . Security::h($a['nome']) . ' - ' . Security::h($a['escola_nome'] ?? '') . '</option>';
}
$conteudo .= '          </select>
                        <small class="text-muted">Segure Ctrl para selecionar múltiplos</small>
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

<div class="modal fade" id="modalVincular" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vincular Aluno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="vincular">
                    <input type="hidden" name="responsavel_id" id="vincularRespId">
                    <p>Responsável: <strong id="vincularRespNome"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Aluno</label>
                        <select name="aluno_id" class="form-select" required>';
foreach ($alunos as $a) {
    $conteudo .= '<option value="' . $a['id'] . '">' . Security::h($a['nome']) . '</option>';
}
$conteudo .= '          </select>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="principal" value="1" class="form-check-input" id="vincularPrincipal">
                        <label class="form-check-label">Responsável principal</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Vincular</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function vincularAluno(id, nome) {
    document.getElementById("vincularRespId").value = id;
    document.getElementById("vincularRespNome").textContent = nome;
    new bootstrap.Modal(document.getElementById("modalVincular")).show();
}
</script>
';

require_once __DIR__ . '/../../views/layouts/admin.php';
