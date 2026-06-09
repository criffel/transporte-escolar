<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Core\Validator;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Models\Motorista;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf  = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $data = $_POST['data_nascimento'] ?? '';

    $v = new Validator(['cpf' => $cpf, 'data_nascimento' => $data]);
    $v->required('cpf', 'CPF')->cpf('cpf');
    $v->required('data_nascimento', 'Data de nascimento')->date('data_nascimento');

    if ($v->passes()) {
        $dados = Motorista::validarIdentificacao($cpf, $data);
        if ($dados) {
            $_SESSION['motorista_identificado'] = $dados;
            $_SESSION['motorista_id'] = $dados['id'];
            header('Location: /motorista/cracha.php');
            exit;
        }
    }

    $erro = 'Dados inválidos. Verifique suas informações e tente novamente.';
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identificação do Motorista — <?= APP_NAME ?></title>
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
                        <i class="bi bi-person-badge text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="text-white mt-3 fw-bold">Identificação do Motorista</h3>
                    <p class="text-white-50">Informe seus dados para gerar o crachá digital</p>
                </div>
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4">
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger"><?= Security::h($erro) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-credit-card-2-front"></i> CPF</label>
                                <input type="text" name="cpf" class="form-control form-control-lg cpf-mask" placeholder="000.000.000-00" required maxlength="14" inputmode="numeric">
                            </div>
                            <div class="mb-4">
                                <label class="form-label"><i class="bi bi-calendar"></i> Data de Nascimento</label>
                                <input type="date" name="data_nascimento" class="form-control form-control-lg" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-qr-code"></i> Gerar Crachá
                            </button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="/login.php" class="text-decoration-none small">
                                <i class="bi bi-arrow-left"></i> Voltar ao Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/public/assets/js/app.js"></script>
</body>
</html>
