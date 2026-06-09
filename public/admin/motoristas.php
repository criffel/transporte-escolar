<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Core\Validator;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Models\Motorista;
use MotoristaCheck\Models\Usuario;
use MotoristaCheck\Models\LogAuditoria;

Auth::exigirPerfil(['admin', 'operador']);

$erro = null;
$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $v = new Validator($_POST);
        $v->required('nome', 'Nome')->required('cpf', 'CPF')->cpf('cpf')
          ->required('data_nascimento', 'Data de nascimento')
          ->required('login', 'Login')->required('senha', 'Senha')->minLength('senha', 6);

        if ($v->passes()) {
            $cpf = preg_replace('/\D/', '', $_POST['cpf']);
            if (Motorista::findByCpf($cpf)) {
                $erro = 'CPF já cadastrado para outro motorista.';
            } else {
                $usuarioId = Usuario::create([
                    'perfil_id' => 3,
                    'nome'      => $_POST['nome'],
                    'email'     => $_POST['email'] ?: "{$_POST['login']}@motorista.local",
                    'login'     => $_POST['login'],
                    'senha'     => $_POST['senha'],
                    'ativo'     => 1,
                ]);

                $foto = null;
                if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $foto = 'motorista_' . $usuarioId . '.' . $ext;
                        move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_DIR . $foto);
                    }
                }

                Motorista::create([
                    'usuario_id'        => $usuarioId,
                    'transportadora_id' => $_POST['transportadora_id'] ?: null,
                    'cpf'               => $cpf,
                    'rg'                => $_POST['rg'] ?? null,
                    'data_nascimento'   => $_POST['data_nascimento'],
                    'foto'              => $foto,
                    'telefone'          => $_POST['telefone'] ?? null,
                    'status'            => $_POST['status'] ?? 'pendente',
                    'observacao'        => $_POST['observacao'] ?? null,
                ]);

                LogAuditoria::registrar('MOTORISTA_CRIADO', "Motorista {$_POST['nome']} cadastrado.");
                $sucesso = 'Motorista cadastrado com sucesso!';
            }
        } else {
            $erro = $v->firstError();
        }
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $motorista = Motorista::findById($id);
        if (!$motorista) { $erro = 'Motorista não encontrado.'; }
        else {
            Motorista::update($id, [
                'transportadora_id' => $_POST['transportadora_id'] ?: null,
                'cpf'               => preg_replace('/\D/', '', $_POST['cpf']),
                'rg'                => $_POST['rg'] ?? null,
                'data_nascimento'   => $_POST['data_nascimento'],
                'telefone'          => $_POST['telefone'] ?? null,
                'status'            => $_POST['status'],
                'observacao'        => $_POST['observacao'] ?? null,
            ]);

            Usuario::update($motorista['usuario_id'], [
                'nome'  => $_POST['nome'],
                'email' => $_POST['email'],
            ]);

            if (!empty($_POST['senha'])) {
                Usuario::update($motorista['usuario_id'], ['senha' => $_POST['senha']]);
            }

            if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $foto = 'motorista_' . $motorista['usuario_id'] . '.' . $ext;
                    move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_DIR . $foto);
                    Motorista::update($id, ['foto' => $foto]);
                }
            }

            LogAuditoria::registrar('MOTORISTA_ATUALIZADO', "Motorista #{$id} atualizado.");
            $sucesso = 'Motorista atualizado com sucesso!';
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $motorista = Motorista::findById($id);
        if ($motorista) {
            Usuario::delete($motorista['usuario_id']);
            LogAuditoria::registrar('MOTORISTA_EXCLUIDO', "Motorista #{$id} excluído.");
            $sucesso = 'Motorista excluído.';
        }
    }
}

$motoristas = Motorista::findAll();
$transportadoras = \MotoristaCheck\Models\Transportadora::findAll();

$titulo = 'Motoristas';

$conteudo = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-badge"></i> Motoristas</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMotorista">
        <i class="bi bi-plus-lg"></i> Novo Motorista
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>Foto</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Transportadora</th>
                        <th>Status</th>
                        <th>Login</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>';

foreach ($motoristas as $m) {
    $foto = $m['foto'] ? '/public/assets/img/upload/' . $m['foto'] : '/public/assets/img/default-avatar.png';
    $statusBadge = \MotoristaCheck\Helpers\Formatador::statusBadge($m['status']);
    $conteudo .= "<tr>
        <td><img src=\"{$foto}\" style=\"width:40px;height:40px;object-fit:cover;border-radius:50%;\" onerror=\"this.src='/public/assets/img/default-avatar.png'\"></td>
        <td>" . Security::h($m['nome']) . "</td>
        <td>" . Security::maskCpf($m['cpf']) . "</td>
        <td>" . Security::h($m['transportadora_nome'] ?? '-') . "</td>
        <td>{$statusBadge}</td>
        <td><small>" . Security::h($m['login'] ?? '-') . "</small></td>
        <td>
            <button class=\"btn btn-sm btn-outline-primary\" onclick=\"editarMotorista(" . json_encode($m) . ")\" title=\"Editar\"><i class=\"bi bi-pencil\"></i></button>
            <form method=\"POST\" style=\"display:inline\" onsubmit=\"return confirm('Excluir este motorista?')\">
                <input type=\"hidden\" name=\"action\" value=\"delete\">
                <input type=\"hidden\" name=\"id\" value=\"{$m['id']}\">
                <button class=\"btn btn-sm btn-outline-danger\" title=\"Excluir\"><i class=\"bi bi-trash\"></i></button>
            </form>
        </td>
    </tr>";
}

$conteudo .= '
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMotorista" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Novo Motorista</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="formId" value="0">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nome completo *</label>
                            <input type="text" name="nome" id="fNome" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Foto</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CPF *</label>
                            <input type="text" name="cpf" id="fCpf" class="form-control cpf-mask" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">RG</label>
                            <input type="text" name="rg" id="fRg" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Data Nascimento *</label>
                            <input type="date" name="data_nascimento" id="fDataNasc" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" id="fTelefone" class="form-control tel-mask">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Transportadora</label>
                            <select name="transportadora_id" id="fTransportadora" class="form-select">
                                <option value="">Nenhuma</option>';

foreach ($transportadoras as $t) {
    $conteudo .= "<option value=\"{$t['id']}\">" . Security::h($t['nome']) . "</option>";
}

$conteudo .= '
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" id="fStatus" class="form-select">
                                <option value="pendente">Pendente</option>
                                <option value="autorizado">Autorizado</option>
                                <option value="bloqueado">Bloqueado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Login *</label>
                            <input type="text" name="login" id="fLogin" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Senha * <small class="text-muted">(mín. 6 caracteres)</small></label>
                            <input type="password" name="senha" id="fSenha" class="form-control" minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" id="fEmail" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observação</label>
                            <textarea name="observacao" id="fObservacao" class="form-control" rows="2"></textarea>
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
function editarMotorista(data) {
    document.getElementById("modalTitle").textContent = "Editar Motorista";
    document.getElementById("formAction").value = "update";
    document.getElementById("formId").value = data.id;
    document.getElementById("fNome").value = data.nome || "";
    document.getElementById("fCpf").value = data.cpf || "";
    document.getElementById("fRg").value = data.rg || "";
    document.getElementById("fDataNasc").value = data.data_nascimento || "";
    document.getElementById("fTelefone").value = data.telefone || "";
    document.getElementById("fTransportadora").value = data.transportadora_id || "";
    document.getElementById("fStatus").value = data.status || "pendente";
    document.getElementById("fLogin").value = data.login || "";
    document.getElementById("fSenha").value = "";
    document.getElementById("fSenha").required = false;
    document.getElementById("fEmail").value = data.email || "";
    document.getElementById("fObservacao").value = data.observacao || "";
    new bootstrap.Modal(document.getElementById("modalMotorista")).show();
}
</script>
';

require_once __DIR__ . '/../../views/layouts/admin.php';
