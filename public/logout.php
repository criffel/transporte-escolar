<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Models\LogAuditoria;

LogAuditoria::registrar('LOGOUT', 'Usuário realizou logout.');
Auth::logout();
header('Location: /login.php');
exit;
