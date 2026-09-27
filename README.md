# InovaSaúde — Login

Tela de login em duas etapas (e-mail → senha) com backend em PHP + MySQL.

## Instalação (cPanel)

1. Envie todos os arquivos para a pasta do site (ex.: `public_html`).
2. No cPanel, crie um banco MySQL e um usuário com acesso a ele.
3. No phpMyAdmin, importe o arquivo `schema.sql`.
4. Copie `inc/config.exemplo.php` para `inc/config.php` e preencha os dados do banco
   e o remetente dos e-mails (crie a conta `nao-responda@inovasaude.com.br` em cPanel > Contas de e-mail).
5. Crie o primeiro usuário pelo Terminal do cPanel:

   ```
   php criar-usuario.php "Seu Nome" admin@inovasaude.com.br "SuaSenhaForte" Administrador
   ```

6. Acesse `https://inovasaude.com.br/login`.

## Arquivos

| Arquivo | Função |
|---|---|
| `index.html` | Tela de login |
| `api/verificar-email.php` | Diz se o e-mail tem conta e devolve o nome |
| `login.php` | Confere a senha e abre a sessão |
| `logout.php` | Encerra a sessão |
| `painel.php` | Página protegida de exemplo (troque pelo painel real) |
| `esqueci-senha.php` | Pede o e-mail e envia o link de redefinição |
| `redefinir-senha.php` | Abre pelo link do e-mail e grava a nova senha |
| `criar-usuario.php` | Cria/atualiza usuários (só pela linha de comando) |
| `schema.sql` | Tabelas `usuarios`, `tentativas_login` e `redefinicoes_senha` |
| `.htaccess` | Rotas `/login`, `/api/verificar-email`, `/esqueci-senha`, `/redefinir-senha`; bloqueia arquivos internos |
| `assets/auth.css` | Visual das páginas de senha |
| `inc/` | Configuração, banco, e-mail e layout (acesso bloqueado pela web) |

## Recuperação de senha

1. Em "Esqueci a senha" a pessoa informa o e-mail (já vem preenchido pela tela de login).
2. Se a conta existir, chega um e-mail com um link válido por 60 minutos e de uso único.
   A resposta na tela é a mesma exista ou não a conta.
3. O link abre a página de nova senha (mínimo 8 caracteres, com letras e números).
4. Ao salvar, a pessoa volta ao login com o aviso "Senha alterada com sucesso".

Se já existir o banco da versão anterior, rode no phpMyAdmin apenas o bloco
`CREATE TABLE redefinicoes_senha` do `schema.sql`.

Segurança: senhas com `password_hash`, proteção CSRF, sessão regenerada no login,
cookies `HttpOnly`/`SameSite` e limite de tentativas por IP (configurável em `inc/config.php`).
