<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/session.php';

use MotoristaCheck\Core\Auth;
use MotoristaCheck\Helpers\Security;
use MotoristaCheck\Helpers\QrCodeHelper;
use MotoristaCheck\Models\Aluno;
use MotoristaCheck\Models\Autorizacao;
use MotoristaCheck\Models\LogAuditoria;
use MotoristaCheck\Models\Retirada;
use MotoristaCheck\Models\Viagem;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado.']);
    exit;
}

$token       = $_POST['token'] ?? '';
$motoristaId = (int)($_POST['motorista_id'] ?? 0);
$alunosIds   = $_POST['alunos'] ?? [];
$status      = $_POST['status'] ?? '';
$motivoRecusa = $_POST['motivo_recusa'] ?? '';

if (empty($token) || empty($motoristaId) || empty($alunosIds) || !in_array($status, ['autorizada', 'recusada'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos para processar.']);
    exit;
}

if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
    exit;
}

$dadosToken = QrCodeHelper::validarToken($token);
if (!$dadosToken) {
    echo json_encode(['success' => false, 'message' => 'QR Code expirado ou inválido. Solicite um novo.']);
    exit;
}

QrCodeHelper::marcarUsado($token);

$resultados = [];
$linksAcompanhamento = [];

foreach ($alunosIds as $alunoId) {
    $alunoId = (int)$alunoId;
    $aluno = Aluno::findById($alunoId);
    if (!$aluno) continue;

    if (!Autorizacao::isAutorizado($motoristaId, $alunoId)) {
        $resultados[] = "{$aluno['nome']}: não autorizado para este motorista.";
        continue;
    }

    $retiradaId = Retirada::create([
        'motorista_id'     => $motoristaId,
        'aluno_id'          => $alunoId,
        'escola_id'         => $aluno['escola_id'],
        'inspetor_id'       => Auth::id(),
        'status'            => $status,
        'motivo_recusa'     => $status === 'recusada' ? $motivoRecusa : null,
        'localizacao_lat'   => $_POST['localizacao_lat'] ?? null,
        'localizacao_lng'   => $_POST['localizacao_lng'] ?? null,
    ]);

    if ($status === 'autorizada') {
        $viagem = Viagem::iniciar($retiradaId, $motoristaId, $alunoId);
        $linkAcompanhamento = APP_URL . '/pai/acompanhar.php?token=' . urlencode($viagem['token']);
        $linksAcompanhamento[] = $linkAcompanhamento;

        if (!empty(SMTP_HOST)) {
            try {
                $responsaveis = \MotoristaCheck\Models\Responsavel::findByAluno($alunoId);
                foreach ($responsaveis as $resp) {
                    if (!empty($resp['email'])) {
                        enviarEmailAcompanhamento($resp['email'], $resp['nome'], $aluno['nome'], $linkAcompanhamento);
                    }
                }
            } catch (\Throwable $e) {
                LogAuditoria::registrar('EMAIL_ERRO', "Erro ao enviar e-mail: " . $e->getMessage());
            }
        }

        $resultados[] = "{$aluno['nome']}: retirada autorizada com sucesso.";
    } else {
        $resultados[] = "{$aluno['nome']}: retirada recusada.";
    }
}

$acaoLabel = $status === 'autorizada' ? 'AUTORIZACAO_RETIRADA' : 'RECUSA_RETIRADA';
LogAuditoria::registrar($acaoLabel, "Inspetor {$status} retirada de " . count($alunosIds) . " aluno(s).", [
    'motorista_id' => $motoristaId,
    'alunos'       => $alunosIds,
    'motivo'       => $motivoRecusa ?: null,
    'token'        => substr($token, 0, 8) . '...',
]);

echo json_encode([
    'success' => true,
    'message' => $status === 'autorizada'
        ? 'Retirada(s) autorizada(s) com sucesso!'
        : 'Retirada(s) recusada(s).',
    'detalhes' => $resultados,
    'link_acompanhamento' => $linksAcompanhamento[0] ?? null,
]);

function enviarEmailAcompanhamento(string $email, string $nome, string $alunoNome, string $link): void
{
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress($email, $nome);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = "🚌 Acompanhe o transporte de {$alunoNome} em tempo real!";
    $mail->isHTML(true);
    $mail->Body = "
        <h3>Olá, {$nome}!</h3>
        <p>O transporte escolar de <strong>{$alunoNome}</strong> já está em andamento.</p>
        <p>Clique no link abaixo para acompanhar em tempo real:</p>
        <p><a href='{$link}' style='display:inline-block;padding:12px 24px;background:#0d6efd;color:white;text-decoration:none;border-radius:6px;font-weight:bold;'>Acompanhar Agora</a></p>
        <p>Ou copie o link: <br><small>{$link}</small></p>
        <p><small>Este link é seguro e expira ao final da viagem.</small></p>
    ";
    $mail->send();
}
