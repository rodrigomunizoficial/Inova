<?php
// Cria ou atualiza um usuário. Uso (pelo Terminal do cPanel ou SSH):
//   php criar-usuario.php "Nome Completo" email@dominio.com.br "senha" Administrador
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/inc/bootstrap.php';

[$_, $nome, $email, $senha, $perfil] = array_pad($argv, 5, null);
$perfil = $perfil ?: 'Solicitante';
if (!$nome || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$senha) {
    fwrite(STDERR, "Uso: php criar-usuario.php \"Nome\" email senha [Administrador|Engenharia|Técnico|Solicitante]\n");
    exit(1);
}
$email = mb_strtolower(trim($email));
$hash  = password_hash($senha, PASSWORD_DEFAULT);

$st = db()->prepare('SELECT id FROM usuarios WHERE email = ?');
$st->execute([$email]);
if ($id = $st->fetchColumn()) {
    db()->prepare('UPDATE usuarios SET nome = ?, senha_hash = ?, perfil = ?, ativo = 1 WHERE id = ?')
        ->execute([$nome, $hash, $perfil, $id]);
    echo "Usuário atualizado: $email\n";
} else {
    db()->prepare('INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES (?, ?, ?, ?)')
        ->execute([$nome, $email, $hash, $perfil]);
    echo "Usuário criado: $email\n";
}
