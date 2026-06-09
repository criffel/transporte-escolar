<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Painel' ?> — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="/public/assets/css/admin.css">
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <?= $head ?? '' ?>
</head>
<body>

<div class="d-flex" id="wrapper">
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="/admin/index.php" class="text-white text-decoration-none">
                <h5 class="mb-0"><i class="bi bi-shield-check"></i> Motorista Check</h5>
                <small class="text-white-50">Painel Administrativo</small>
            </a>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['SCRIPT_NAME']) === 'index.php' ? 'active' : '' ?>" href="/admin/index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'], 'motoristas') ? 'active' : '' ?>" href="/admin/motoristas.php">
                    <i class="bi bi-person-badge"></i> Motoristas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'], 'alunos') ? 'active' : '' ?>" href="/admin/alunos.php">
                    <i class="bi bi-people"></i> Alunos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'], 'responsaveis') ? 'active' : '' ?>" href="/admin/responsaveis.php">
                    <i class="bi bi-person-heart"></i> Responsáveis
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'], 'escolas') ? 'active' : '' ?>" href="/admin/escolas.php">
                    <i class="bi bi-building"></i> Escolas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'], 'veiculos') ? 'active' : '' ?>" href="/admin/veiculos.php">
                    <i class="bi bi-truck"></i> Veículos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'], 'transportadoras') ? 'active' : '' ?>" href="/admin/transportadoras.php">
                    <i class="bi bi-building-gear"></i> Transportadoras
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'], 'autorizacoes') ? 'active' : '' ?>" href="/admin/autorizacoes.php">
                    <i class="bi bi-check2-square"></i> Autorizações
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'], 'viagens') ? 'active' : '' ?>" href="/admin/viagens.php">
                    <i class="bi bi-geo-alt"></i> Viagens
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'], 'logs') ? 'active' : '' ?>" href="/admin/logs.php">
                    <i class="bi bi-journal-text"></i> Logs
                </a>
            </li>
            <?php if (\MotoristaCheck\Core\Auth::isAdmin()): ?>
            <li class="nav-item">
                <a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'], 'usuarios') ? 'active' : '' ?>" href="/admin/usuarios.php">
                    <i class="bi bi-key"></i> Usuários
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item mt-4">
                <a class="nav-link text-white-50" href="/logout.php">
                    <i class="bi bi-box-arrow-left"></i> Sair
                </a>
            </li>
        </ul>
    </div>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-3">
            <div class="container-fluid">
                <button class="btn btn-outline-primary d-md-none" id="menu-toggle">
                    <i class="bi bi-list"></i>
                </button>
                <span class="navbar-text ms-auto">
                    <i class="bi bi-person-circle"></i>
                    <?= \MotoristaCheck\Helpers\Security::h(\MotoristaCheck\Core\Auth::usuarioLogado()['nome'] ?? '') ?>
                    <span class="badge bg-primary ms-1"><?= \MotoristaCheck\Core\Auth::perfil() ?></span>
                </span>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= \MotoristaCheck\Helpers\Security::h($erro) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if (isset($sucesso)): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= \MotoristaCheck\Helpers\Security::h($sucesso) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?= $conteudo ?? '' ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script src="/public/assets/js/admin.js"></script>
<script>
document.getElementById('menu-toggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('active');
});
</script>
<?= $scripts ?? '' ?>
</body>
</html>
