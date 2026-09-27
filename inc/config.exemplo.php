<?php
// Copie este arquivo para inc/config.php e preencha com os dados do seu servidor.
// O inc/config.php não vai para o Git.

return [
    // Banco de dados MySQL (dados do cPanel > Bancos de dados MySQL)
    'db_dsn'  => 'mysql:host=localhost;dbname=inovasaude;charset=utf8mb4',
    'db_user' => 'usuario_do_banco',
    'db_pass' => 'senha_do_banco',

    // Para onde o usuário vai depois de entrar
    'redirect_apos_login' => '/painel.php',

    // Limite de tentativas por IP (verificação de e-mail + senha) na janela abaixo
    'max_tentativas'   => 30,
    'janela_minutos'   => 15,
];
