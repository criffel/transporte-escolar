<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Negado - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-5">
                        <h1 class="display-1 text-danger mb-4">403</h1>
                        <h3 class="mb-3">Acesso Negado</h3>
                        <p class="text-muted mb-4">Você não tem permissão para acessar esta página.</p>
                        <a href="/login.php" class="btn btn-primary">Voltar ao Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
