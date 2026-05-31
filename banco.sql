DROP DATABASE IF EXISTS plataforma_servicos;
CREATE DATABASE plataforma_servicos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE plataforma_servicos;


CREATE TABLE usuario (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nome       VARCHAR(100)  NOT NULL,
  email      VARCHAR(100)  UNIQUE NOT NULL,
  senha      VARCHAR(255)  NOT NULL,
  telefone   VARCHAR(20),
  endereco   VARCHAR(150),
  cidade     VARCHAR(100),
  estado     VARCHAR(50),
  tipo       ENUM('cliente','empresa','admin') DEFAULT 'cliente',
  criado_em  DATETIME DEFAULT NOW()
);


CREATE TABLE empresa (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNIQUE,
  nome       VARCHAR(100) NOT NULL,
  cnpj       VARCHAR(20)  UNIQUE,
  email      VARCHAR(100),
  telefone   VARCHAR(20),
  endereco   VARCHAR(150),
  cidade     VARCHAR(100),
  estado     VARCHAR(50),
  descricao  TEXT,
  ativo      TINYINT(1) DEFAULT 1,
  criado_em  DATETIME DEFAULT NOW(),
  FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
);


CREATE TABLE categoria (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  nome  VARCHAR(100) NOT NULL UNIQUE,
  icone VARCHAR(10)  DEFAULT '🔧'
);


CREATE TABLE servico (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id  INT NOT NULL,
  categoria_id INT,
  nome        VARCHAR(100)   NOT NULL,
  descricao   TEXT,
  preco_medio DECIMAL(10,2),
  ativo       TINYINT(1) DEFAULT 1,
  criado_em   DATETIME DEFAULT NOW(),
  FOREIGN KEY (empresa_id)   REFERENCES empresa(id)   ON DELETE CASCADE,
  FOREIGN KEY (categoria_id) REFERENCES categoria(id) ON DELETE SET NULL
);


CREATE TABLE solicitacao (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id        INT NOT NULL,
  empresa_id        INT NOT NULL,
  descricao         TEXT NOT NULL,
  prioridade        ENUM('Baixa','Média','Alta') DEFAULT 'Média',
  status            ENUM('Pendente','Em andamento','Concluído','Cancelado') DEFAULT 'Pendente',
  resposta          TEXT,
  data_solicitacao  DATETIME DEFAULT NOW(),
  data_conclusao    DATE,
  FOREIGN KEY (usuario_id) REFERENCES usuario(id)  ON DELETE CASCADE,
  FOREIGN KEY (empresa_id) REFERENCES empresa(id)  ON DELETE CASCADE
);


CREATE TABLE avaliacao (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id    INT NOT NULL,
  empresa_id    INT NOT NULL,
  solicitacao_id INT UNIQUE,
  nota          INT NOT NULL CHECK (nota >= 1 AND nota <= 5),
  comentario    TEXT,
  moderado      TINYINT(1) DEFAULT 0,
  data          DATETIME DEFAULT NOW(),
  FOREIGN KEY (usuario_id)     REFERENCES usuario(id)      ON DELETE CASCADE,
  FOREIGN KEY (empresa_id)     REFERENCES empresa(id)      ON DELETE CASCADE,
  FOREIGN KEY (solicitacao_id) REFERENCES solicitacao(id)  ON DELETE SET NULL
);



INSERT INTO categoria (nome, icone) VALUES
  ('Manutenção',  '🔧'),
  ('Reforma',     '🏠'),
  ('Transporte',  '🚚'),
  ('Tecnologia',  '💻'),
  ('Limpeza',     '🧹'),
  ('Jardinagem',  '🌿'),
  ('Segurança',   '🔒'),
  ('Saúde',       '🏥');

INSERT INTO usuario (nome, email, senha, tipo) VALUES
  ('Administrador', 'admin@opus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT INTO usuario (nome, email, senha, telefone, cidade, estado, tipo) VALUES
  ('João Silva', 'joao@demo.com', '$2y$10$TKh8H1.PkwiDfDpwkixdQ.3vcSdnCWuuKJnk2GClWYN.xUHVWXefi', '(44) 99999-1111', 'Cianorte', 'PR', 'cliente');

INSERT INTO usuario (nome, email, senha, telefone, cidade, estado, tipo) VALUES
  ('EletroLuz Admin', 'eletroluz@demo.com', '$2y$10$TKh8H1.PkwiDfDpwkixdQ.3vcSdnCWuuKJnk2GClWYN.xUHVWXefi', '(44) 99999-2222', 'Cianorte', 'PR', 'empresa'),
  ('Fretes Paraná Admin', 'fretes@demo.com', '$2y$10$TKh8H1.PkwiDfDpwkixdQ.3vcSdnCWuuKJnk2GClWYN.xUHVWXefi', '(44) 99999-3333', 'Cianorte', 'PR', 'empresa'),
  ('TechHelp Admin', 'techhelp@demo.com', '$2y$10$TKh8H1.PkwiDfDpwkixdQ.3vcSdnCWuuKJnk2GClWYN.xUHVWXefi', '(44) 99999-4444', 'Cianorte', 'PR', 'empresa');

INSERT INTO empresa (usuario_id, nome, cnpj, email, telefone, cidade, estado, descricao) VALUES
  (3, 'EletroLuz Cianorte', '12.345.678/0001-90', 'contato@eletroluz.com', '(44) 3372-1111', 'Cianorte', 'PR', 'Atendimento residencial e comercial, manutenção preventiva, troca de disjuntores e instalação de tomadas. Mais de 10 anos de experiência no mercado elétrico.'),
  (4, 'Fretes Paraná',      '98.765.432/0001-10', 'contato@fretespr.com',  '(44) 3372-2222', 'Cianorte', 'PR', 'Equipe especializada em fretes urbanos, montagem simples e transporte seguro de móveis e eletrodomésticos.'),
  (5, 'TechHelp',           '55.444.333/0001-20', 'contato@techhelp.com',  '(44) 3372-3333', 'Cianorte', 'PR', 'Formatação, manutenção, instalação de sistemas e suporte técnico para empresas e residências.');

INSERT INTO servico (empresa_id, categoria_id, nome, descricao, preco_medio) VALUES
  (1, 1, 'Instalação Elétrica',   'Instalação completa de tomadas, interruptores e quadro elétrico.', 350.00),
  (1, 1, 'Manutenção Preventiva', 'Revisão geral do sistema elétrico residencial.',                   200.00),
  (1, 1, 'Troca de Disjuntor',   'Substituição de disjuntores defeituosos ou subdimensionados.',      120.00),
  (2, 3, 'Frete Urbano',          'Transporte de móveis e objetos dentro da cidade.',                 150.00),
  (2, 3, 'Pequena Mudança',       'Mudança completa para residências de até 2 quartos.',              400.00),
  (3, 4, 'Formatação PC',         'Formatação e reinstalação do sistema operacional.',                 90.00),
  (3, 4, 'Suporte Técnico',       'Diagnóstico e resolução de problemas em computadores e notebooks.', 80.00),
  (3, 4, 'Instalação de Rede',    'Configuração de redes Wi-Fi e cabeadas para empresas.',            250.00);

INSERT INTO solicitacao (usuario_id, empresa_id, descricao, prioridade, status, resposta, data_conclusao) VALUES
  (2, 1, 'Preciso instalar 4 tomadas novas no quarto e sala.', 'Média', 'Concluído', 'Orçamento aceito. Serviço realizado em 2h. Tomadas instaladas conforme solicitado.', '2026-05-20'),
  (2, 3, 'Meu notebook não liga mais, preciso de diagnóstico.', 'Alta', 'Em andamento', 'Diagnóstico realizado: fonte danificada. Aguardando peça.', NULL);

INSERT INTO avaliacao (usuario_id, empresa_id, solicitacao_id, nota, comentario) VALUES
  (2, 1, 1, 5, 'Serviço excelente! Profissional pontual e muito cuidadoso. Recomendo!');
