<?php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME * 60);

    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME * 60,
        'path'     => '/',
        'domain'   => '',
        'secure'   => SESSION_SECURE,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_name('MOTORISTA_CHECK_SID');
    session_start();
}

if (!isset($_SESSION['_created'])) {
    session_regenerate_id(true);
    $_SESSION['_created'] = time();
}

if (isset($_SESSION['_created']) && (time() - $_SESSION['_created'] > (SESSION_LIFETIME * 60))) {
    session_destroy();
    session_start();
    session_regenerate_id(true);
    $_SESSION['_created'] = time();
}
