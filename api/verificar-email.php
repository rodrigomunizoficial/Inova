<?php
// POST { "email": "..." } → { "encontrado": bool, "nome": string, "csrf": string }
require __DIR__ . '/../inc/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

$dados = json_decode(file_get_contents('php://input') ?: '', true);
$email = is_array($dados) ? trim((string) ($dados['email'] ?? '')) : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['erro' => 'E-mail inválido']);
    exit;
}

if (bloqueado('email')) {
    http_response_code(429);
    echo json_encode(['erro' => 'Muitas tentativas. Aguarde alguns minutos e tente novamente.']);
    exit;
}

$u = buscar_usuario($email);
$_SESSION['email_verificado'] = $u ? $u['email'] : null;

echo json_encode([
    'encontrado' => (bool) $u,
    'nome'       => $u['nome'] ?? '',
    'csrf'       => csrf_token(),
], JSON_UNESCAPED_UNICODE);
