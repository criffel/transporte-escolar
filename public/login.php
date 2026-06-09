<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Core\Validator;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Models\LogAuditoria;

if (Auth::check()) {
    $redirect = match (Auth::perfil()) {
        'admin', 'operador' => '/admin/index.php',
        'motorista'         => '/motorista/cracha.php',
        'escola'            => '/inspetor/escanear.php',
        default             => '/login.php',
    };
    header("Location: $redirect");
    exit;
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v = new Validator($_POST);
    $v->required('login', 'Usuário')->required('senha', 'Senha');

    if ($v->passes()) {
        $login = Security::sanitize($_POST['login']);
        $senha = $_POST['senha'];

        if (Auth::tentarLogin($login, $senha)) {
            LogAuditoria::registrar('LOGIN', "Usuário {$login} realizou login.");
            $redirect = match (Auth::perfil()) {
                'admin', 'operador' => '/admin/index.php',
                'motorista'         => '/motorista/cracha.php',
                'escola'            => '/inspetor/escanear.php',
                'responsavel'       => '/pai/acompanhar.php',
                default             => '/login.php',
            };
            header("Location: $redirect");
            exit;
        } else {
            $erro = 'Usuário ou senha inválidos.';
        }
    } else {
        $erro = $v->firstError();
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-md-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle shadow" style="width: 90px; height: 90px;">
                        <i class="bi bi-shield-check text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="text-white mt-3 fw-bold"><?= APP_NAME ?></h3>
                    <p class="text-white-50">Sistema de Controle de Transporte Escolar</p>
                </div>
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4">
                        <h5 class="card-title text-center mb-4">
                            <i class="bi bi-lock"></i> Acessar o Sistema
                        </h5>

                        <?php if ($erro): ?>
                            <div class="alert alert-danger"><?= Security::h($erro) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-person"></i> Usuário</label>
                                <input type="text" name="login" class="form-control form-control-lg" placeholder="Digite seu usuário" required autofocus>
                            </div>
                            <div class="mb-4">
                                <label class="form-label"><i class="bi bi-key"></i> Senha</label>
                                <input type="password" name="senha" class="form-control form-control-lg" placeholder="Digite sua senha" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Entrar
                            </button>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <a href="/motorista/identificar.php" class="text-decoration-none">
                                <i class="bi bi-qr-code"></i> Sou Motorista — Acessar Crachá
                            </a>
                        </div>
                    </div>
                </div>
                <p class="text-center text-white-50 mt-3 small">
                    &copy; <?= date('Y') ?> Motorista Check. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
