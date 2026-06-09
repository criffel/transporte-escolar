<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;

Auth::exigirPerfil(['admin', 'escola', 'operador']);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escanear QR Code — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-0"><i class="bi bi-qr-code-scan"></i> Escanear QR Code</h4>
                        <small class="text-muted">Aponte a câmera para o QR Code do motorista</small>
                    </div>
                    <a href="/logout.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-box-arrow-left"></i> Sair
                    </a>
                </div>

                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 text-center">
                        <div id="qr-reader" class="mx-auto" style="max-width: 400px;"></div>
                        <div id="qr-reader-results" class="mt-3"></div>
                        <div class="mt-3" id="manual-input" style="display:none;">
                            <hr>
                            <p class="text-muted small">Ou digite o token manualmente:</p>
                            <form id="manual-form" action="/inspetor/validar.php" method="GET" class="input-group">
                                <input type="text" name="token" class="form-control" placeholder="Cole o token aqui" required>
                                <button class="btn btn-primary" type="submit">Validar</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button class="btn btn-link text-decoration-none" onclick="document.getElementById('manual-input').style.display='block'">
                        <i class="bi bi-keyboard"></i> Digitar token manualmente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
    function onScanSuccess(decodedText, decodedResult) {
        document.getElementById('qr-reader-results').innerHTML =
            '<div class="alert alert-success"><i class="bi bi-check-circle"></i> QR Code identificado! Redirecionando...</div>';
        setTimeout(function() {
            window.location.href = decodedText;
        }, 800);
    }

    function onScanFailure(error) {
        console.warn('Scan error:', error);
    }

    let html5QrCode = new Html5Qrcode("qr-reader");
    html5QrCode.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: { width: 280, height: 280 }
        },
        onScanSuccess,
        onScanFailure
    ).catch(function(err) {
        document.getElementById('qr-reader').innerHTML =
            '<div class="alert alert-warning"><i class="bi bi-camera-video-off"></i> Não foi possível acessar a câmera. ' +
            '<br><small>Verifique as permissões ou use o campo de token manual abaixo.</small></div>';
        document.getElementById('manual-input').style.display = 'block';
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
