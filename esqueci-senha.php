<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/email.php';

$email  = trim((string) ($_POST['email'] ?? $_GET['email'] ?? ''));
$erro   = '';
$enviado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['_csrf'] ?? null)) {
        $erro = 'Sua sessão expirou. Tente novamente.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } elseif (bloqueado('reset')) {
        $erro = 'Muitas tentativas seguidas. Aguarde alguns minutos e tente novamente.';
    } else {
        $u = buscar_usuario($email);
        if ($u) {
            $minutos = (int) ($CONFIG['redefinicao_minutos'] ?? 60);
            $token   = bin2hex(random_bytes(32));
            $agora   = date('Y-m-d H:i:s');

            // Um link novo invalida os anteriores ainda não usados
            db()->prepare('UPDATE redefinicoes_senha SET usado_em = ? WHERE usuario_id = ? AND usado_em IS NULL')
                ->execute([$agora, $u['id']]);
            db()->prepare('INSERT INTO redefinicoes_senha (usuario_id, token_hash, expira_em, criado_em) VALUES (?, ?, ?, ?)')
                ->execute([$u['id'], hash('sha256', $token), date('Y-m-d H:i:s', time() + 60 * $minutos), $agora]);

            $link = rtrim($CONFIG['url_base'], '/') . '/redefinir-senha?token=' . $token;
            [$html, $texto] = email_redefinicao($u['nome'], $link, $minutos);
            if (!enviar_email($u['email'], 'Redefinição de senha · InovaSaúde', $html, $texto)) {
                error_log('InovaSaúde: falha ao enviar e-mail de redefinição para ' . $u['email']);
            }
        }
        // Mesma resposta exista ou não a conta
        $enviado = true;
    }
}

pagina_inicio('Esqueci a senha');

if ($enviado): ?>
  <div class="hero-ic ok">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m22 7-10 6L2 7"/></svg>
  </div>
  <h1>Confira seu e-mail 📬</h1>
  <p class="sub">Se existir uma conta com <b><?= h($email) ?></b>, enviamos um link para você criar uma nova senha. Ele vale por <?= (int) ($CONFIG['redefinicao_minutos'] ?? 60) ?> minutos.</p>
  <p class="sub" style="margin-top:14px;font-size:14px">Não chegou? Olhe a caixa de spam ou lixo eletrônico. Se ainda assim não aparecer em alguns minutos, peça um novo link.</p>
  <form method="get" action="/esqueci-senha" style="margin-top:22px">
    <input type="hidden" name="email" value="<?= h($email) ?>">
    <button type="submit" class="submit" style="background:rgba(255,255,255,.06);box-shadow:none;border:1px solid rgba(255,255,255,.12)">Pedir um novo link</button>
  </form>
<?php else: ?>
  <div class="hero-ic">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="15" r="4"/><path d="m10.8 12.2 9.2-9.2M17 6l3 3M14 9l2 2"/></svg>
  </div>
  <h1>Esqueceu a senha?</h1>
  <p class="sub">Acontece com todo mundo! Informe o e-mail da sua conta e enviaremos um link para você criar uma nova senha.</p>

  <form method="post" action="/esqueci-senha" novalidate>
    <?= $erro ? alerta(h($erro)) : '' ?>
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <div>
      <label for="email">E-mail</label>
      <div class="control">
        <svg class="lead" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m22 7-10 6L2 7"/></svg>
        <input id="email" name="email" type="email" inputmode="email" autocomplete="username" placeholder="seu@email.com.br" value="<?= h($email) ?>" required autofocus>
      </div>
    </div>
    <button type="submit" class="submit">
      Enviar link
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4z"/></svg>
    </button>
  </form>
<?php endif;

pagina_fim();
