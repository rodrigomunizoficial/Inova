<?php
declare(strict_types=1);

function enviar_email(string $para, string $assunto, string $html, string $texto): bool
{
    global $CONFIG;

    if (!empty($CONFIG['email_log'])) {
        $conteudo = "Para: $para\nAssunto: $assunto\n\n$texto\n\n" . str_repeat('-', 60) . "\n";
        return file_put_contents($CONFIG['email_log'], $conteudo, FILE_APPEND) !== false;
    }

    $limite = 'b' . bin2hex(random_bytes(8));
    $de     = sprintf('=?UTF-8?B?%s?= <%s>', base64_encode($CONFIG['email_nome']), $CONFIG['email_remetente']);
    $headers = implode("\r\n", [
        'From: ' . $de,
        'Reply-To: ' . $CONFIG['email_remetente'],
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $limite . '"',
    ]);
    $corpo = "--$limite\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
           . chunk_split(base64_encode($texto))
           . "--$limite\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
           . chunk_split(base64_encode($html))
           . "--$limite--";

    return mail($para, '=?UTF-8?B?' . base64_encode($assunto) . '?=', $corpo, $headers, '-f' . $CONFIG['email_remetente']);
}

function email_redefinicao(string $nome, string $link, int $minutos): array
{
    $n = htmlspecialchars(explode(' ', trim($nome))[0] ?: $nome, ENT_QUOTES, 'UTF-8');
    $l = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

    $html = <<<HTML
<!doctype html>
<html lang="pt-BR"><body style="margin:0;padding:0;background:#f2f2f8;font-family:Arial,Helvetica,sans-serif;color:#1d1d35">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f2f8;padding:32px 12px">
<tr><td align="center">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:18px;overflow:hidden">
    <tr><td style="background:linear-gradient(120deg,#6c5cff,#3aa8f0);background-color:#6c5cff;padding:28px 32px;color:#fff">
      <div style="font-size:20px;font-weight:800;letter-spacing:.02em">INOVA<span style="color:#b8f3ff">SAÚDE</span></div>
      <div style="font-size:13px;opacity:.85;margin-top:4px">tecnologia a serviço da vida</div>
    </td></tr>
    <tr><td style="padding:32px">
      <h1 style="margin:0 0 12px;font-size:22px">Olá, {$n}!</h1>
      <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#4a4a66">Recebemos um pedido para redefinir a senha da sua conta. Clique no botão abaixo para criar uma nova senha:</p>
      <p style="margin:0 0 24px"><a href="{$l}" style="display:inline-block;background:#6c5cff;color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 26px;border-radius:12px">Criar nova senha</a></p>
      <p style="margin:0 0 8px;font-size:13px;color:#6b6b8a">Este link vale por {$minutos} minutos e só pode ser usado uma vez.</p>
      <p style="margin:0 0 20px;font-size:13px;color:#6b6b8a">Se você não pediu isso, pode ignorar este e-mail — sua senha continua a mesma.</p>
      <p style="margin:0;font-size:12px;color:#9a9ab8;word-break:break-all">Se o botão não funcionar, copie e cole no navegador:<br>{$l}</p>
    </td></tr>
  </table>
</td></tr>
</table>
</body></html>
HTML;

    $texto = "Olá, {$nome}!\n\nRecebemos um pedido para redefinir a senha da sua conta na InovaSaúde.\n"
           . "Para criar uma nova senha, acesse:\n{$link}\n\n"
           . "O link vale por {$minutos} minutos e só pode ser usado uma vez.\n"
           . "Se você não pediu isso, ignore este e-mail.";

    return [$html, $texto];
}
