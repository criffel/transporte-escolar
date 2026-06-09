<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Models\Veiculo;
use MotoristaCheck\Models\Motorista;
use MotoristaCheck\Models\LogAuditoria;

Auth::exigirPerfil(['admin', 'operador']);

$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        Veiculo::create($_POST);
        LogAuditoria::registrar('VEICULO_CRIADO', "Veículo placa {$_POST['placa']} cadastrado.");
        $sucesso = 'Veículo cadastrado.';
    }
    if ($action === 'update') {
        Veiculo::update((int)$_POST['id'], $_POST);
        $sucesso = 'Veículo atualizado.';
    }
    if ($action === 'delete') {
        Veiculo::delete((int)$_POST['id']);
        $sucesso = 'Veículo excluído.';
    }
}

$veiculos = Veiculo::findAll();
$motoristas = Motorista::findAll();

$titulo = 'Veículos';
$conteudo = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-truck"></i> Veículos</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalVeiculo"><i class="bi bi-plus-lg"></i> Novo Veículo</button>
</div>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 datatable">
                <thead class="table-light">
                    <tr><th>Motorista</th><th>Placa</th><th>Modelo</th><th>Cor</th><th>Ano</th><th>Capac.</th><th>Ativo</th><th>Ações</th></tr>
                </thead>
                <tbody>';
foreach ($veiculos as $v) {
    $conteudo .= '<tr>
        <td>' . Security::h($v['motorista_nome']) . '</td>
        <td><strong>' . Security::h($v['placa']) . '</strong></td>
        <td>' . Security::h($v['modelo']) . '</td>
        <td>' . Security::h($v['cor']) . '</td>
        <td>' . ($v['ano'] ?? '-') . '</td>
        <td>' . $v['capacidade'] . '</td>
        <td>' . ($v['ativo'] ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>') . '</td>
        <td>
            <button class="btn btn-sm btn-outline-primary" onclick="editarVeiculo(' . json_encode($v) . ')"><i class="bi bi-pencil"></i></button>
            <form method="POST" style="display:inline" onsubmit="return confirm(\'Excluir?\')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' . $v['id'] . '">
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

<div class="modal fade" id="modalVeiculo" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVeiculoTitle">Novo Veículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formVeiculoAction" value="create">
                    <input type="hidden" name="id" id="formVeiculoId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Motorista *</label>
                        <select name="motorista_id" id="fVeiculoMotorista" class="form-select" required>';
foreach ($motoristas as $m) {
    $conteudo .= '<option value="' . $m['id'] . '">' . Security::h($m['nome']) . '</option>';
}
$conteudo .= '          </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Placa *</label>
                            <input type="text" name="placa" id="fVeiculoPlaca" class="form-control" required maxlength="8" style="text-transform:uppercase">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modelo *</label>
                            <input type="text" name="modelo" id="fVeiculoModelo" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cor *</label>
                            <input type="text" name="cor" id="fVeiculoCor" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ano</label>
                            <input type="number" name="ano" id="fVeiculoAno" class="form-control" min="2000" max="' . date('Y') . '">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Capacidade</label>
                            <input type="number" name="capacidade" id="fVeiculoCapacidade" class="form-control" value="1" min="1">
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
function editarVeiculo(d) {
    document.getElementById("modalVeiculoTitle").textContent = "Editar Veículo";
    document.getElementById("formVeiculoAction").value = "update";
    document.getElementById("formVeiculoId").value = d.id;
    document.getElementById("fVeiculoMotorista").value = d.motorista_id;
    document.getElementById("fVeiculoPlaca").value = d.placa;
    document.getElementById("fVeiculoModelo").value = d.modelo;
    document.getElementById("fVeiculoCor").value = d.cor;
    document.getElementById("fVeiculoAno").value = d.ano || "";
    document.getElementById("fVeiculoCapacidade").value = d.capacidade;
    new bootstrap.Modal(document.getElementById("modalVeiculo")).show();
}
</script>
';

require_once __DIR__ . '/../../views/layouts/admin.php';
