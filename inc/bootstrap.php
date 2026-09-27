<?php
declare(strict_types=1);

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Configuração ausente: copie inc/config.exemplo.php para inc/config.php.');
}
$CONFIG = require $configFile;

session_name('inova_sess');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

function db(): PDO
{
    static $pdo = null;
    global $CONFIG;
    if ($pdo === null) {
        $pdo = new PDO($CONFIG['db_dsn'], $CONFIG['db_user'] ?? null, $CONFIG['db_pass'] ?? null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function ip_cliente(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valido(?string $t): bool
{
    return is_string($t) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

/** Registra uma tentativa e informa se o IP passou do limite. */
function bloqueado(string $tipo): bool
{
    global $CONFIG;
    $desde = date('Y-m-d H:i:s', time() - 60 * (int) $CONFIG['janela_minutos']);
    $st = db()->prepare('SELECT COUNT(*) FROM tentativas_login WHERE ip = ? AND criado_em > ?');
    $st->execute([ip_cliente(), $desde]);
    if ((int) $st->fetchColumn() >= (int) $CONFIG['max_tentativas']) {
        return true;
    }
    db()->prepare('INSERT INTO tentativas_login (ip, tipo, criado_em) VALUES (?, ?, ?)')
        ->execute([ip_cliente(), $tipo, date('Y-m-d H:i:s')]);
    return false;
}

function limpar_tentativas(): void
{
    db()->prepare('DELETE FROM tentativas_login WHERE ip = ?')->execute([ip_cliente()]);
}

function buscar_usuario(string $email): ?array
{
    $st = db()->prepare('SELECT id, nome, email, senha_hash, perfil FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1');
    $st->execute([mb_strtolower(trim($email))]);
    return $st->fetch() ?: null;
}

function usuario_logado(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function exigir_login(): array
{
    $u = usuario_logado();
    if (!$u) {
        header('Location: /login');
        exit;
    }
    return $u;
}
