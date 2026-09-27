<?php
declare(strict_types=1);

function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function pagina_inicio(string $titulo): void
{
    ?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= h($titulo) ?> · InovaSaúde</title>
<meta name="theme-color" content="#0b0b1a">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/auth.css">
</head>
<body>
<div class="backdrop" aria-hidden="true"><div class="blob b1"></div><div class="blob b2"></div><div class="grid"></div></div>
<main class="card">
  <a class="brand" href="/login" aria-label="InovaSaúde — voltar ao login">
    <svg width="48" height="48" viewBox="0 0 56 56" aria-hidden="true">
      <defs><linearGradient id="lg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#7b6bff"/><stop offset=".55" stop-color="#5b4bff"/><stop offset="1" stop-color="#2fb8e8"/></linearGradient></defs>
      <rect width="56" height="56" rx="15" fill="url(#lg)"/>
      <rect x=".5" y=".5" width="55" height="55" rx="14.5" fill="none" stroke="#fff" stroke-opacity=".22"/>
      <path d="M22 12h12a2 2 0 0 1 2 2v8h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-8v8a2 2 0 0 1-2 2H22a2 2 0 0 1-2-2v-8h-8a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h8v-8a2 2 0 0 1 2-2z" fill="#fff"/>
      <path d="M6 40h8l3-6 4 11 3-5h26" fill="none" stroke="#9ff0ff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      <circle cx="45" cy="11" r="2.2" fill="#c8f6ff"/>
    </svg>
    <div><div class="brand-name">INOVA<span>SAÚDE</span></div><span class="brand-tag">tecnologia a serviço da vida</span></div>
  </a>
<?php
}

function pagina_fim(string $scripts = ''): void
{
    ?>
  <a class="back" href="/login">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
    Voltar para o login
  </a>
</main>
<?= $scripts ?>
</body>
</html>
<?php
}

function alerta(string $html): string
{
    return '<div class="alert" role="alert"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span>' . $html . '</span></div>';
}
