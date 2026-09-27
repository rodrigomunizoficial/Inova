<?php
require __DIR__ . '/inc/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login');
    exit;
}

$email = trim((string) ($_POST['email'] ?? ''));
$senha = (string) ($_POST['password'] ?? '');

function falhar(string $email, string $motivo): never
{
    header('Location: /login?erro=' . $motivo . '&email=' . rawurlencode($email));
    exit;
}

if (!csrf_valido($_POST['_csrf'] ?? null)) {
    falhar($email, 'sessao');
}
if (bloqueado('senha')) {
    falhar($email, 'bloqueado');
}

$u = buscar_usuario($email);
if (!$u || !password_verify($senha, $u['senha_hash'])) {
    falhar($email, 'senha');
}

if (password_needs_rehash($u['senha_hash'], PASSWORD_DEFAULT)) {
    db()->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')
        ->execute([password_hash($senha, PASSWORD_DEFAULT), $u['id']]);
}

limpar_tentativas();
session_regenerate_id(true);
unset($_SESSION['csrf'], $_SESSION['email_verificado']);
$_SESSION['usuario'] = [
    'id'     => (int) $u['id'],
    'nome'   => $u['nome'],
    'email'  => $u['email'],
    'perfil' => $u['perfil'],
];
db()->prepare('UPDATE usuarios SET ultimo_login = ? WHERE id = ?')
    ->execute([date('Y-m-d H:i:s'), $u['id']]);

header('Location: ' . $CONFIG['redirect_apos_login']);
