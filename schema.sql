-- Tabelas do login da InovaSaúde (MySQL / MariaDB)

CREATE TABLE IF NOT EXISTS usuarios (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome          VARCHAR(120) NOT NULL,
  email         VARCHAR(190) NOT NULL UNIQUE,
  senha_hash    VARCHAR(255) NOT NULL,
  perfil        ENUM('Administrador','Engenharia','Técnico','Solicitante') NOT NULL DEFAULT 'Solicitante',
  ativo         TINYINT(1) NOT NULL DEFAULT 1,
  ultimo_login  DATETIME NULL,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tentativas_login (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip         VARCHAR(45) NOT NULL,
  tipo       VARCHAR(10) NOT NULL,
  criado_em  DATETIME NOT NULL,
  INDEX idx_ip_data (ip, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS redefinicoes_senha (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT UNSIGNED NOT NULL,
  token_hash  CHAR(64) NOT NULL UNIQUE,
  expira_em   DATETIME NOT NULL,
  usado_em    DATETIME NULL,
  criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_usuario (usuario_id),
  CONSTRAINT fk_redef_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
