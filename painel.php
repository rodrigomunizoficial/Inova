<?php
// Página protegida de exemplo — substitua pelo painel real do sistema.
require __DIR__ . '/inc/bootstrap.php';
$u = exigir_login();
$h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Painel · InovaSaúde</title>
<style>
  body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #07071a; color: #f4f4fb;
         font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }
  .card { padding: 40px; border-radius: 24px; background: rgba(20,20,44,.8); border: 1px solid rgba(255,255,255,.08); text-align: center; }
  a { display: inline-block; margin-top: 20px; color: #a99fff; }
</style>
</head>
<body>
  <div class="card">
    <h1>Olá, <?= $h($u['nome']) ?>!</h1>
    <p>Você entrou como <strong><?= $h($u['perfil']) ?></strong> (<?= $h($u['email']) ?>).</p>
    <a href="/logout.php">Sair</a>
  </div>
</body>
</html>
