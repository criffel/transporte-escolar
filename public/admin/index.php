<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Models\Motorista;
use MotoristaCheck\Models\Retirada;
use MotoristaCheck\Models\Viagem;
use MotoristaCheck\Models\Aluno;
use MotoristaCheck\Models\Escola;
use MotoristaCheck\Models\Transportadora;

Auth::exigirPerfil(['admin', 'operador']);

$totalMotoristas = Motorista::count();
$motoristasAutorizados = Motorista::countByStatus('autorizado');
$motoristasPendentes = Motorista::countByStatus('pendente');
$motoristasBloqueados = Motorista::countByStatus('bloqueado');

$totalAlunos = Aluno::count();
$totalEscolas = Escola::count();
$totalTransportadoras = Transportadora::count();

$retiradasHoje = Retirada::countToday();
$autorizadasHoje = Retirada::countTodayByStatus('autorizada');
$recusadasHoje = Retirada::countTodayByStatus('recusada');

$viagensAndamento = Viagem::countEmAndamento();
$viagensHoje = Viagem::countFinalizadasHoje();

$retiradasRecentes = Retirada::hoje();

$titulo = 'Dashboard';

$conteudo = '
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-speedometer2"></i> Dashboard</h4>
    <small class="text-muted">' . date('d/m/Y H:i') . '</small>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-start border-primary border-4 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Motoristas</p>
                        <h3 class="fw-bold mb-0">' . $totalMotoristas . '</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded p-2">
                        <i class="bi bi-person-badge text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <div class="mt-2 small">
                    <span class="text-success">' . $motoristasAutorizados . ' autorizados</span> ·
                    <span class="text-warning">' . $motoristasPendentes . ' pendentes</span> ·
                    <span class="text-danger">' . $motoristasBloqueados . ' bloqueados</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-success border-4 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Alunos</p>
                        <h3 class="fw-bold mb-0">' . $totalAlunos . '</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded p-2">
                        <i class="bi bi-people text-success" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">' . $totalEscolas . ' escolas</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-warning border-4 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Retiradas Hoje</p>
                        <h3 class="fw-bold mb-0">' . $retiradasHoje . '</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded p-2">
                        <i class="bi bi-check2-square text-warning" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <div class="mt-2 small">
                    <span class="text-success">' . $autorizadasHoje . ' autorizadas</span> ·
                    <span class="text-danger">' . $recusadasHoje . ' recusadas</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-info border-4 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Viagens</p>
                        <h3 class="fw-bold mb-0">' . $viagensAndamento . '</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded p-2">
                        <i class="bi bi-geo-alt text-info" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">' . $viagensHoje . ' finalizadas hoje</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between">
                <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart"></i> Motoristas por Status</h6>
            </div>
            <div class="card-body">
                <canvas id="chartMotoristas" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between">
                <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart"></i> Retiradas Hoje</h6>
            </div>
            <div class="card-body">
                <canvas id="chartRetiradas" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Retiradas de Hoje</h6>
        <span class="badge bg-primary">' . $retiradasHoje . ' registro(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Motorista</th>
                        <th>Aluno</th>
                        <th>Escola</th>
                        <th>Status</th>
                        <th>Inspetor</th>
                        <th>Horário</th>
                    </tr>
                </thead>
                <tbody>';

if (empty($retiradasRecentes)) {
    $conteudo .= '<tr><td colspan="6" class="text-center text-muted py-4">Nenhuma retirada hoje.</td></tr>';
} else {
    foreach ($retiradasRecentes as $r) {
        $badge = $r["status"] === "autorizada"
            ? "<span class=\"badge bg-success\"><i class=\"bi bi-check-circle\"></i> Autorizada</span>"
            : "<span class=\"badge bg-danger\"><i class=\"bi bi-x-circle\"></i> Recusada</span>";
        $conteudo .= "<tr>
            <td>" . \MotoristaCheck\Helpers\Security::h($r["motorista_nome"]) . "</td>
            <td>" . \MotoristaCheck\Helpers\Security::h($r["aluno_nome"]) . "</td>
            <td>" . \MotoristaCheck\Helpers\Security::h($r["escola_nome"]) . "</td>
            <td>{$badge}</td>
            <td>" . \MotoristaCheck\Helpers\Security::h($r["inspetor_nome"] ?? "-") . "</td>
            <td>" . \MotoristaCheck\Helpers\Formatador::dataHora($r["data_hora"]) . "</td>
        </tr>";
    }
}

$conteudo .= '
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const ctxMotoristas = document.getElementById("chartMotoristas");
if (ctxMotoristas) {
    new Chart(ctxMotoristas, {
        type: "pie",
        data: {
            labels: ["Autorizados (' . $motoristasAutorizados . ')", "Pendentes (' . $motoristasPendentes . ')", "Bloqueados (' . $motoristasBloqueados . ')"],
            datasets: [{
                data: [' . $motoristasAutorizados . ', ' . $motoristasPendentes . ', ' . $motoristasBloqueados . '],
                backgroundColor: ["#198754", "#ffc107", "#dc3545"],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: "bottom" },
            }
        }
    });
}

const ctxRetiradas = document.getElementById("chartRetiradas");
if (ctxRetiradas) {
    new Chart(ctxRetiradas, {
        type: "doughnut",
        data: {
            labels: ["Autorizadas (' . $autorizadasHoje . ')", "Recusadas (' . $recusadasHoje . ')"],
            datasets: [{
                data: [' . $autorizadasHoje . ', ' . $recusadasHoje . '],
                backgroundColor: ["#198754", "#dc3545"],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: "bottom" },
            }
        }
    });
}
</script>
';

require_once __DIR__ . '/../../views/layouts/admin.php';
