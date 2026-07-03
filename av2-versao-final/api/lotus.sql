-- ============================================================
--  LÓTUS STUDIO 
-- ============================================================
 
CREATE DATABASE IF NOT EXISTS lotus_studio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lotus_studio;
 
-- ------------------------------------------------------------
-- usuarios
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(150)        NOT NULL,
    email       VARCHAR(190)        NOT NULL,
    senha_hash  VARCHAR(255)        NOT NULL,
    criado_em   TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 
-- ------------------------------------------------------------
-- unidades
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS unidades (
    id        VARCHAR(30)   PRIMARY KEY,
    nome      VARCHAR(100)  NOT NULL,
    endereco  VARCHAR(255)  NOT NULL,
    nota      DECIMAL(2,1)  DEFAULT NULL,
    reviews   VARCHAR(20)   DEFAULT NULL,
    img       VARCHAR(255)  DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 
-- ------------------------------------------------------------
-- servicos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS servicos (
    id        VARCHAR(30)     PRIMARY KEY,
    nome      VARCHAR(150)    NOT NULL,
    duracao   VARCHAR(50)     DEFAULT NULL,
    preco     DECIMAL(10,2)   NOT NULL,
    img       VARCHAR(255)    DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 
-- ------------------------------------------------------------
-- agendamentos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS agendamentos (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id        INT UNSIGNED       DEFAULT NULL,
    protocolo         VARCHAR(30)        NOT NULL,
    nome_cliente      VARCHAR(150)       NOT NULL,
    unidade_id        VARCHAR(30)        NOT NULL,
    data_agendamento  DATE               NOT NULL,
    hora_agendamento  TIME               NOT NULL,
    pagamento         VARCHAR(20)        NOT NULL,
    cupom             VARCHAR(50)        DEFAULT NULL,
    total             DECIMAL(10,2)      NOT NULL,
    criado_em         TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP,
 
    UNIQUE KEY uq_agendamentos_protocolo (protocolo),
    KEY idx_agendamentos_unidade (unidade_id),
    KEY idx_agendamentos_usuario (usuario_id),
 
    CONSTRAINT fk_agendamentos_unidade
        FOREIGN KEY (unidade_id) REFERENCES unidades(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
 
    CONSTRAINT fk_agendamentos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 
-- ------------------------------------------------------------
-- agendamento_servicos  (tabela associativa N:N)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS agendamento_servicos (
    agendamento_id  INT UNSIGNED    NOT NULL,
    servico_id      VARCHAR(30)     NOT NULL,
    preco_cobrado   DECIMAL(10,2)   NOT NULL,
 
    PRIMARY KEY (agendamento_id, servico_id),
 
    CONSTRAINT fk_agsrv_agendamento
        FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id)
        ON DELETE CASCADE,
 
    CONSTRAINT fk_agsrv_servico
        FOREIGN KEY (servico_id) REFERENCES servicos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 
-- ------------------------------------------------------------
INSERT INTO unidades (id, nome, endereco, nota, reviews, img) VALUES
('barra',      'Barra da Tijuca', 'Avenida das Americas - Barra Shopping, Piso 2, Loja 301',    5, '3.233', 'img/barradatijuca-img.png'),
('recreio',    'Recreio',         'Avenida das Americas - America Shopping, Piso 1, Loja 127',  5, '1.445', 'img/recreio-img.png'),
('botafogo',   'Botafogo',        'Voluntarios da Patria, n 145 - Terceiro Andar, 104',         5, '2.333', 'img/botafogo-img.png'),
('copacabana', 'Copacabana',      'Siqueira Campos - Metro, Loja E 106',                        5, '4.255', 'img/copacabana-img.png')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);
 
INSERT INTO servicos (id, nome, duracao, preco, img) VALUES
('relax',  'Massagem Relaxante', '30 min | 1h | 1:30h', 99.00, 'img/massagem-relaxante.png'),
('facial', 'Massagem Facial',    '30 min | 1h',          89.90, 'img/massagem-facial-img.png'),
('vacuo',  'Vacuo terapia',      '50 min',               99.00, 'img/vacuo-terapia-img.png'),
('aroma',  'Terapia Aromatica',  '1:30h',                99.00, 'img/terapia-aromatica-img.png')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);
 