<?php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
$uri = $uri ?: '/';

$publicRoutes = [
    '/login.php'              => __DIR__ . '/public/login.php',
    '/logout.php'             => __DIR__ . '/public/logout.php',
    '/motorista/identificar.php' => __DIR__ . '/public/motorista/identificar.php',
    '/motorista/cracha.php'       => __DIR__ . '/public/motorista/cracha.php',
    '/inspetor/escanear.php'      => __DIR__ . '/public/inspetor/escanear.php',
    '/inspetor/validar.php'       => __DIR__ . '/public/inspetor/validar.php',
    '/pai/acompanhar.php'         => __DIR__ . '/public/pai/acompanhar.php',
];

$apiRoutes = [
    '/api/localizacao.php'         => __DIR__ . '/api/localizacao.php',
    '/api/localizacao-pai.php'     => __DIR__ . '/api/localizacao-pai.php',
    '/api/validar-qrcode.php'      => __DIR__ . '/api/validar-qrcode.php',
    '/api/autorizar-retirada.php'  => __DIR__ . '/api/autorizar-retirada.php',
    '/api/finalizar-viagem.php'    => __DIR__ . '/api/finalizar-viagem.php',
];

$adminRoutes = [
    '/admin/index.php'             => __DIR__ . '/public/admin/index.php',
    '/admin/motoristas.php'        => __DIR__ . '/public/admin/motoristas.php',
    '/admin/alunos.php'            => __DIR__ . '/public/admin/alunos.php',
    '/admin/responsaveis.php'      => __DIR__ . '/public/admin/responsaveis.php',
    '/admin/escolas.php'           => __DIR__ . '/public/admin/escolas.php',
    '/admin/veiculos.php'          => __DIR__ . '/public/admin/veiculos.php',
    '/admin/transportadoras.php'   => __DIR__ . '/public/admin/transportadoras.php',
    '/admin/autorizacoes.php'      => __DIR__ . '/public/admin/autorizacoes.php',
    '/admin/viagens.php'           => __DIR__ . '/public/admin/viagens.php',
    '/admin/logs.php'              => __DIR__ . '/public/admin/logs.php',
    '/admin/usuarios.php'          => __DIR__ . '/public/admin/usuarios.php',
];

$allRoutes = array_merge($publicRoutes, $apiRoutes, $adminRoutes);

if (isset($allRoutes[$uri])) {
    $file = $allRoutes[$uri];

    if (isset($adminRoutes[$uri])) {
        Auth::exigirLogin();
        if (!Auth::isAdmin() && !Auth::isOperador()) {
            http_response_code(403);
            require __DIR__ . '/views/errors/403.php';
            exit;
        }
    }

    if (isset($apiRoutes[$uri])) {
        header('Content-Type: application/json; charset=utf-8');
    }

    require $file;
    exit;
}

if (str_starts_with($uri, '/public/assets/')) {
    $assetPath = __DIR__ . $uri;
    if (file_exists($assetPath)) {
        $ext = pathinfo($assetPath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'ico'  => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2'=> 'font/woff2',
        ];
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        readfile($assetPath);
        exit;
    }
}

if (Auth::check()) {
    $perfil = Auth::perfil();
    if (in_array($perfil, ['admin', 'operador'])) {
        header('Location: /admin/index.php');
    } elseif ($perfil === 'motorista') {
        header('Location: /motorista/cracha.php');
    } elseif ($perfil === 'escola') {
        header('Location: /inspetor/escanear.php');
    } else {
        header('Location: /login.php');
    }
} else {
    header('Location: /login.php');
}
exit;
