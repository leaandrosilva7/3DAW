-- ============================================================
--  LÓTUS STUDIO — Banco de Dados
-- ============================================================

CREATE DATABASE IF NOT EXISTS lotus_studio
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE lotus_studio;

-- ------------------------------------------------------------
-- Tabela: unidades
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS unidades (
  id         VARCHAR(20)  NOT NULL PRIMARY KEY,
  nome       VARCHAR(100) NOT NULL,
  endereco   VARCHAR(255) NOT NULL,
  nota       DECIMAL(2,1) NOT NULL DEFAULT 5.0,
  reviews    VARCHAR(10)  NOT NULL DEFAULT '0',
  img        VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO unidades (id, nome, endereco, nota, reviews, img) VALUES
  ('barra',       'Barra da Tijuca', 'Avenida das Américas — Barra Shopping, Piso 2, Loja 301',     5.0, '3.233', 'img/barradatijuca-img.png'),
  ('recreio',     'Recreio',         'Avenida das Américas — America Shopping, Piso 1, Loja 127',   5.0, '1.445', 'img/recreio-img.png'),
  ('botafogo',    'Botafogo',        'Voluntários da Pátria, nº 145 — Terceiro Andar, 104',         5.0, '2.333', 'img/botafogo-img.png'),
  ('copacabana',  'Copacabana',      'Siqueira Campos — Metrô, Loja E 106',                         5.0, '4.255', 'img/copacabana-img.png')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- ------------------------------------------------------------
-- Tabela: servicos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS servicos (
  id       VARCHAR(20)    NOT NULL PRIMARY KEY,
  nome     VARCHAR(100)   NOT NULL,
  duracao  VARCHAR(50)    NOT NULL,
  preco    DECIMAL(10,2)  NOT NULL,
  img      VARCHAR(255)   NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO servicos (id, nome, duracao, preco, img) VALUES
  ('relax',  'Massagem Relaxante', '30 min | 1h | 1:30h', 99.00, 'img/massagem-relaxante.png'),
  ('facial', 'Massagem Facial',    '30 min | 1h',          89.90, 'img/massagem-facial-img.png'),
  ('vacuo',  'Vácuo terapia',      '50 min',               99.00, 'img/vacuo-terapia-img.png'),
  ('aroma',  'Terapia Aromática',  '1:30h',                99.00, 'img/terapia-aromatica-img.png')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- ------------------------------------------------------------
-- Tabela: metodos_pagamento
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS metodos_pagamento (
  id     VARCHAR(20)  NOT NULL PRIMARY KEY,
  nome   VARCHAR(50)  NOT NULL,
  classe VARCHAR(30)  NOT NULL,
  img    VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO metodos_pagamento (id, nome, classe, img) VALUES
  ('pix',     'Pix',     'pix',     'img/pix-img.png'),
  ('credito', 'Crédito', 'credito', 'img/card-img.png'),
  ('debito',  'Débito',  'debito',  'img/card-img.png')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- ------------------------------------------------------------
-- Tabela: agendamentos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS agendamentos (
  id             INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
  protocolo      VARCHAR(20)     NOT NULL UNIQUE,
  nome_cliente   VARCHAR(150)    NOT NULL,
  unidade_id     VARCHAR(20)     NOT NULL,
  pagamento_id   VARCHAR(20)     NOT NULL,
  cupom          VARCHAR(50)         NULL DEFAULT NULL,
  total          DECIMAL(10,2)   NOT NULL,
  status         ENUM('pendente','confirmado','cancelado') NOT NULL DEFAULT 'confirmado',
  criado_em      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (unidade_id)   REFERENCES unidades(id),
  FOREIGN KEY (pagamento_id) REFERENCES metodos_pagamento(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabela: agendamento_servicos  (pivot N:N)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS agendamento_servicos (
  agendamento_id INT UNSIGNED NOT NULL,
  servico_id     VARCHAR(20)  NOT NULL,
  PRIMARY KEY (agendamento_id, servico_id),
  FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE,
  FOREIGN KEY (servico_id)     REFERENCES servicos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;