-- ============================================================
-- Motorista Check - Sistema de Controle de Transporte Escolar
-- Database: MySQL 8.0
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS motorista_check
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE motorista_check;

-- -----------------------------------------------------------
-- 1. perfis
-- -----------------------------------------------------------
CREATE TABLE perfis (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(50)  NOT NULL UNIQUE,
    descricao   VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 2. usuarios
-- -----------------------------------------------------------
CREATE TABLE usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    perfil_id       INT          NOT NULL,
    nome            VARCHAR(150) NOT NULL,
    email           VARCHAR(255) NOT NULL UNIQUE,
    login           VARCHAR(100) NOT NULL UNIQUE,
    senha           VARCHAR(255) NOT NULL,
    ativo           TINYINT(1) DEFAULT 1,
    ultimo_acesso   DATETIME     NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (perfil_id) REFERENCES perfis(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 3. transportadoras
-- -----------------------------------------------------------
CREATE TABLE transportadoras (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(200) NOT NULL,
    cnpj        VARCHAR(18)  NOT NULL UNIQUE,
    telefone    VARCHAR(20)  NULL,
    contato     VARCHAR(150) NULL,
    endereco    TEXT         NULL,
    ativo       TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 4. motoristas
-- -----------------------------------------------------------
CREATE TABLE motoristas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id          INT          NOT NULL,
    transportadora_id   INT          NULL,
    cpf                 VARCHAR(14)  NOT NULL UNIQUE,
    rg                  VARCHAR(20)  NULL,
    data_nascimento     DATE         NOT NULL,
    foto                VARCHAR(255) NULL,
    telefone            VARCHAR(20)  NULL,
    status              ENUM('autorizado','bloqueado','pendente') DEFAULT 'pendente',
    ativo               TINYINT(1) DEFAULT 1,
    observacao          TEXT         NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (transportadora_id) REFERENCES transportadoras(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 5. veiculos
-- -----------------------------------------------------------
CREATE TABLE veiculos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    motorista_id    INT          NOT NULL,
    placa           VARCHAR(10)  NOT NULL UNIQUE,
    modelo          VARCHAR(100) NOT NULL,
    cor             VARCHAR(50)  NOT NULL,
    ano             INT          NULL,
    capacidade      INT          DEFAULT 1,
    ativo           TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (motorista_id) REFERENCES motoristas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 6. escolas
-- -----------------------------------------------------------
CREATE TABLE escolas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(200) NOT NULL,
    endereco    TEXT         NULL,
    telefone    VARCHAR(20)  NULL,
    contato     VARCHAR(150) NULL,
    ativo       TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 7. alunos
-- -----------------------------------------------------------
CREATE TABLE alunos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    escola_id       INT          NOT NULL,
    nome            VARCHAR(150) NOT NULL,
    data_nascimento DATE         NULL,
    serie           VARCHAR(50)  NULL,
    foto            VARCHAR(255) NULL,
    observacao      TEXT         NULL,
    ativo           TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (escola_id) REFERENCES escolas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 8. responsaveis
-- -----------------------------------------------------------
CREATE TABLE responsaveis (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT          NOT NULL,
    nome            VARCHAR(150) NOT NULL,
    cpf             VARCHAR(14)  NOT NULL UNIQUE,
    telefone        VARCHAR(20)  NULL,
    parentesco      VARCHAR(50)  NULL,
    ativo           TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 9. responsavel_aluno
-- -----------------------------------------------------------
CREATE TABLE responsavel_aluno (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    responsavel_id  INT NOT NULL,
    aluno_id        INT NOT NULL,
    principal       TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_resp_aluno (responsavel_id, aluno_id),
    FOREIGN KEY (responsavel_id) REFERENCES responsaveis(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 10. autorizacoes_motorista_aluno
-- -----------------------------------------------------------
CREATE TABLE autorizacoes_motorista_aluno (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    motorista_id    INT NOT NULL,
    aluno_id        INT NOT NULL,
    escola_id       INT NOT NULL,
    ativo           TINYINT(1) DEFAULT 1,
    data_inicio     DATE NULL,
    data_fim        DATE NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_motorista_aluno (motorista_id, aluno_id),
    FOREIGN KEY (motorista_id) REFERENCES motoristas(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (escola_id) REFERENCES escolas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 11. qrcode_tokens
-- -----------------------------------------------------------
CREATE TABLE qrcode_tokens (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    motorista_id    INT          NOT NULL,
    token           VARCHAR(128) NOT NULL UNIQUE,
    expira_em       DATETIME     NOT NULL,
    usado           TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_expira (expira_em),
    FOREIGN KEY (motorista_id) REFERENCES motoristas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 12. retiradas
-- -----------------------------------------------------------
CREATE TABLE retiradas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    motorista_id    INT          NOT NULL,
    aluno_id        INT          NOT NULL,
    escola_id       INT          NOT NULL,
    inspetor_id     INT          NULL,
    status          ENUM('autorizada','recusada') NOT NULL,
    motivo_recusa   TEXT         NULL,
    localizacao_lat DECIMAL(10,8) NULL,
    localizacao_lng DECIMAL(11,8) NULL,
    data_hora       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (motorista_id) REFERENCES motoristas(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (escola_id) REFERENCES escolas(id) ON DELETE RESTRICT,
    FOREIGN KEY (inspetor_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 13. viagens
-- -----------------------------------------------------------
CREATE TABLE viagens (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    retirada_id             INT          NOT NULL,
    motorista_id            INT          NOT NULL,
    aluno_id                INT          NOT NULL,
    token_acompanhamento    VARCHAR(128) NOT NULL UNIQUE,
    status                  ENUM('em_andamento','finalizada','cancelada') DEFAULT 'em_andamento',
    inicio                  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fim                     DATETIME     NULL,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token_viagem (token_acompanhamento),
    FOREIGN KEY (retirada_id) REFERENCES retiradas(id) ON DELETE CASCADE,
    FOREIGN KEY (motorista_id) REFERENCES motoristas(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 14. localizacoes_viagem
-- -----------------------------------------------------------
CREATE TABLE localizacoes_viagem (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    viagem_id   INT           NOT NULL,
    latitude    DECIMAL(10,8) NOT NULL,
    longitude   DECIMAL(11,8) NOT NULL,
    velocidade  DECIMAL(5,2)  NULL,
    data_hora   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_viagem (viagem_id),
    INDEX idx_data (data_hora),
    FOREIGN KEY (viagem_id) REFERENCES viagens(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 15. logs_auditoria
-- -----------------------------------------------------------
CREATE TABLE logs_auditoria (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT          NULL,
    acao        VARCHAR(100) NOT NULL,
    descricao   TEXT         NULL,
    ip          VARCHAR(45)  NULL,
    user_agent  VARCHAR(500) NULL,
    dados       JSON         NULL,
    data_hora   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_acao (acao),
    INDEX idx_data (data_hora),
    INDEX idx_usuario (usuario_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERTS INICIAIS
-- ============================================================

INSERT INTO perfis (nome, descricao) VALUES
    ('admin', 'Administrador do sistema'),
    ('escola', 'Inspetor da escola'),
    ('motorista', 'Motorista de transporte escolar'),
    ('responsavel', 'Pai ou responsável pelo aluno'),
    ('operador', 'Operador da transportadora');

-- Senha: @Admin123 (hash gerado com password_hash)
INSERT INTO usuarios (perfil_id, nome, email, login, senha) VALUES
    (1, 'Administrador Master', 'admin@motoristacheck.com.br', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Escola padrão
INSERT INTO escolas (nome, endereco, telefone, contato) VALUES
    ('Escola Municipal Exemplo', 'Rua das Flores, 123 - Centro', '(11) 99999-8888', 'Secretaria');

-- Transportadora padrão
INSERT INTO transportadoras (nome, cnpj, telefone, contato) VALUES
    ('Transportadora Escolar Segura Ltda', '00.000.000/0001-00', '(11) 98888-7777', 'João da Silva');
