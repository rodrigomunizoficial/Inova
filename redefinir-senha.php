<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

$token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
$erro  = '';

function buscar_redefinicao(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }
    $st = db()->prepare(
        'SELECT r.id, r.usuario_id, u.nome, u.email
           FROM redefinicoes_senha r JOIN usuarios u ON u.id = r.usuario_id
          WHERE r.token_hash = ? AND r.usado_em IS NULL AND r.expira_em > ? AND u.ativo = 1
          LIMIT 1'
    );
    $st->execute([hash('sha256', $token), date('Y-m-d H:i:s')]);
    return $st->fetch() ?: null;
}

/** Devolve a mensagem de erro ou '' quando a senha é aceitável. */
function validar_senha(string $senha, string $confirmar): string
{
    if (strlen($senha) < 8) {
        return 'A senha precisa ter pelo menos 8 caracteres.';
    }
    if (!preg_match('/\pL/u', $senha) || !preg_match('/\d/', $senha)) {
        return 'Use letras e números na senha.';
    }
    if ($senha !== $confirmar) {
        return 'As duas senhas não são iguais.';
    }
    return '';
}

$r = buscar_redefinicao($token);

if ($r && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha = (string) ($_POST['senha'] ?? '');
    if (!csrf_valido($_POST['_csrf'] ?? null)) {
        $erro = 'Sua sessão expirou. Tente novamente.';
    } elseif ($erro = validar_senha($senha, (string) ($_POST['confirmar'] ?? ''))) {
        // mensagem já definida
    } else {
        $agora = date('Y-m-d H:i:s');
        db()->beginTransaction();
        db()->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')
            ->execute([password_hash($senha, PASSWORD_DEFAULT), $r['usuario_id']]);
        db()->prepare('UPDATE redefinicoes_senha SET usado_em = ? WHERE usuario_id = ? AND usado_em IS NULL')
            ->execute([$agora, $r['usuario_id']]);
        db()->commit();

        unset($_SESSION['csrf']);
        header('Location: /login?ok=senha&email=' . rawurlencode($r['email']));
        exit;
    }
}

pagina_inicio('Nova senha');

if (!$r): ?>
  <div class="hero-ic" style="color:#ffb3c4;border-color:rgba(255,107,138,.35);background:rgba(255,107,138,.12)">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
  </div>
  <h1>Este link não vale mais</h1>
  <p class="sub">O link de redefinição expirou ou já foi usado. Por segurança, cada link funciona uma única vez. Peça um novo — leva só alguns segundos.</p>
  <a class="submit" href="/esqueci-senha" style="margin-top:26px">Pedir um novo link</a>
<?php else: ?>
  <div class="hero-ic">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/><circle cx="12" cy="16" r="1"/></svg>
  </div>
  <h1>Crie sua nova senha</h1>
  <p class="sub">Quase lá, <b><?= h(explode(' ', trim($r['nome']))[0]) ?></b>! Escolha uma senha forte para <b><?= h($r['email']) ?></b>.</p>

  <form method="post" action="/redefinir-senha" novalidate id="f">
    <?= $erro ? alerta(h($erro)) : '' ?>
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="token" value="<?= h($token) ?>">
    <input type="text" name="username" value="<?= h($r['email']) ?>" autocomplete="username" hidden>
    <div>
      <label for="senha">Nova senha</label>
      <div class="control">
        <svg class="lead" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
        <input id="senha" name="senha" type="password" autocomplete="new-password" placeholder="Mínimo de 8 caracteres" required autofocus>
        <button type="button" class="toggle" data-alvo="senha" aria-label="Mostrar senha">
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <div class="meter" id="meter" data-n="0"><i></i><i></i><i></i><i></i></div>
      <ul class="rules" id="rules">
        <li data-r="len">8 ou mais caracteres</li>
        <li data-r="num">Letras e números</li>
        <li data-r="case">Maiúscula e minúscula</li>
        <li data-r="sym">Um símbolo (!@#…)</li>
      </ul>
    </div>
    <div>
      <label for="confirmar">Confirme a nova senha</label>
      <div class="control">
        <svg class="lead" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        <input id="confirmar" name="confirmar" type="password" autocomplete="new-password" placeholder="Digite novamente" required>
      </div>
      <p class="hint" id="igual">&nbsp;</p>
    </div>
    <button type="submit" class="submit" id="salvar">
      Salvar nova senha
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </button>
  </form>
<?php endif;

pagina_fim(<<<'JS'
<script>
(function () {
  var s = document.getElementById('senha'); if (!s) return;
  var c = document.getElementById('confirmar'), meter = document.getElementById('meter');
  var igual = document.getElementById('igual'), salvar = document.getElementById('salvar');
  var rules = { len: function (v) { return v.length >= 8; }, num: function (v) { return /\p{L}/u.test(v) && /\d/.test(v); },
                case: function (v) { return /[a-z]/.test(v) && /[A-Z]/.test(v); }, sym: function (v) { return /[^\p{L}\d\s]/u.test(v); } };
  function upd() {
    var n = 0;
    document.querySelectorAll('#rules li').forEach(function (li) { var ok = rules[li.dataset.r](s.value); li.classList.toggle('ok', ok); if (ok) n++; });
    meter.dataset.n = s.value ? String(Math.max(1, n)) : '0';
    if (!c.value) { igual.innerHTML = '&nbsp;'; igual.style.color = ''; }
    else if (c.value === s.value) { igual.textContent = '✓ As senhas são iguais'; igual.style.color = '#7ff3d3'; }
    else { igual.textContent = 'As senhas ainda não são iguais'; igual.style.color = '#ff9bb0'; }
    salvar.disabled = !(rules.len(s.value) && rules.num(s.value) && c.value === s.value);
  }
  s.addEventListener('input', upd); c.addEventListener('input', upd); upd();
  document.querySelectorAll('.toggle').forEach(function (b) {
    b.addEventListener('click', function () {
      var show = s.type === 'password'; s.type = c.type = show ? 'text' : 'password';
      b.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha'); s.focus();
    });
  });
})();
</script>
JS);
