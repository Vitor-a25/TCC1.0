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
  id           INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id   INT NOT NULL,
  categoria_id INT,
  nome         VARCHAR(100) NOT NULL,
  descricao    TEXT,
  preco_medio  DECIMAL(15,2),
  ativo        TINYINT(1) DEFAULT 1,
  criado_em    DATETIME DEFAULT NOW(),
  FOREIGN KEY (empresa_id)   REFERENCES empresa(id)   ON DELETE CASCADE,
  FOREIGN KEY (categoria_id) REFERENCES categoria(id) ON DELETE SET NULL
);

CREATE TABLE funcionario (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT NOT NULL,
  nome       VARCHAR(100) NOT NULL,
  telefone   VARCHAR(20),
  cargo      VARCHAR(100),
  ativo      TINYINT(1) DEFAULT 1,
  criado_em  DATETIME DEFAULT NOW(),
  FOREIGN KEY (empresa_id) REFERENCES empresa(id) ON DELETE CASCADE
);

CREATE TABLE solicitacao (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id       INT NOT NULL,
  empresa_id       INT NOT NULL,
  descricao        TEXT NOT NULL,
  prioridade       ENUM('Baixa','Média','Alta') DEFAULT 'Média',
  status           ENUM('Pendente','Em andamento','Concluído','Cancelado') DEFAULT 'Pendente',
  resposta         TEXT,
  data_solicitacao DATETIME DEFAULT NOW(),
  data_conclusao   DATE,
  funcionario_id   INT,
  FOREIGN KEY (usuario_id)    REFERENCES usuario(id)     ON DELETE CASCADE,
  FOREIGN KEY (empresa_id)    REFERENCES empresa(id)     ON DELETE CASCADE,
  FOREIGN KEY (funcionario_id) REFERENCES funcionario(id) ON DELETE SET NULL
);

CREATE TABLE avaliacao (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id     INT NOT NULL,
  empresa_id     INT NOT NULL,
  solicitacao_id INT UNIQUE,
  nota           INT NOT NULL CHECK (nota >= 1 AND nota <= 5),
  comentario     TEXT,
  moderado       TINYINT(1) DEFAULT 0,
  data           DATETIME DEFAULT NOW(),
  FOREIGN KEY (usuario_id)     REFERENCES usuario(id)     ON DELETE CASCADE,
  FOREIGN KEY (empresa_id)     REFERENCES empresa(id)     ON DELETE CASCADE,
  FOREIGN KEY (solicitacao_id) REFERENCES solicitacao(id) ON DELETE SET NULL
);

CREATE TABLE backup_log (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  arquivo    VARCHAR(255),
  tamanho    INT,
  criado_em  DATETIME DEFAULT NOW()
);

-- CATEGORIAS
INSERT INTO categoria (nome, icone) VALUES
  ('Manutenção', '🔧'),
  ('Reforma',    '🏠'),
  ('Transporte', '🚚'),
  ('Tecnologia', '💻'),
  ('Limpeza',    '🧹'),
  ('Jardinagem', '🌿'),
  ('Segurança',  '🔒'),
  ('Saúde',      '🏥');

-- ADMIN
INSERT INTO usuario (nome, email, senha, tipo) VALUES
  ('Administrador', 'admin@opus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- CLIENTES
INSERT INTO usuario (nome, email, senha, telefone, endereco, cidade, estado, tipo) VALUES
  ('João Silva',       'joao@demo.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-1111', 'Rua das Acácias, 145, Jardim Paulista',      'Cianorte', 'PR', 'cliente'),
  ('Maria Souza',      'maria@demo.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-2222', 'Rua Paraná, 320, Centro',                   'Cianorte', 'PR', 'cliente'),
  ('Carlos Oliveira',  'carlos@demo.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-3333', 'Av. Brasil, 890, Jardim Alvorada',          'Cianorte', 'PR', 'cliente'),
  ('Ana Paula Lima',   'ana@demo.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-4444', 'Rua Minas Gerais, 210, Vila Nova',          'Cianorte', 'PR', 'cliente'),
  ('Pedro Almeida',    'pedro@demo.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-5555', 'Rua São Paulo, 78, Jardim Santa Cruz',      'Maringá',  'PR', 'cliente'),
  ('Fernanda Costa',   'fernanda@demo.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-6666', 'Av. Colombo, 440, Zona 07',                 'Maringá',  'PR', 'cliente');

-- EMPRESAS (usuarios)
INSERT INTO usuario (nome, email, senha, telefone, cidade, estado, tipo) VALUES
  ('EletroLuz Resp',       'eletroluz@demo.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1001', 'Cianorte', 'PR', 'empresa'),
  ('Fretes PR Resp',       'fretes@demo.com',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1002', 'Cianorte', 'PR', 'empresa'),
  ('TechHelp Resp',        'techhelp@demo.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1003', 'Cianorte', 'PR', 'empresa'),
  ('HidroMaster Resp',     'hidro@demo.com',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1004', 'Cianorte', 'PR', 'empresa'),
  ('PintaBem Resp',        'pintabem@demo.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1005', 'Cianorte', 'PR', 'empresa'),
  ('LimpezaTotal Resp',    'limpeza@demo.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1006', 'Cianorte', 'PR', 'empresa'),
  ('JardimVivo Resp',      'jardim@demo.com',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1007', 'Cianorte', 'PR', 'empresa'),
  ('SegurMax Resp',        'segurmax@demo.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1008', 'Cianorte', 'PR', 'empresa'),
  ('SaúdeEmCasa Resp',     'saude@demo.com',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1009', 'Cianorte', 'PR', 'empresa'),
  ('ReformasPR Resp',      'reformas@demo.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1010', 'Cianorte', 'PR', 'empresa'),
  ('ArGelado Resp',        'argelado@demo.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1011', 'Cianorte', 'PR', 'empresa'),
  ('DedetizadoraCia Resp', 'dedetizadora@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1012', 'Cianorte', 'PR', 'empresa'),
  ('TechMar Resp',         'techmar@demo.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1013', 'Maringá',  'PR', 'empresa'),
  ('MudançasFácil Resp',   'mudancas@demo.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1014', 'Maringá',  'PR', 'empresa'),
  ('LimpeMax Resp',        'limpemax@demo.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1015', 'Maringá',  'PR', 'empresa');

-- EMPRESAS
INSERT INTO empresa (usuario_id, nome, cnpj, email, telefone, endereco, cidade, estado, descricao) VALUES
  (8,  'EletroLuz Cianorte',    '12.345.678/0001-90', 'contato@eletroluz.com',    '(44) 3372-1111', 'Rua Paraná, 145, Centro',               'Cianorte', 'PR', 'Atendimento residencial e comercial, manutenção preventiva, troca de disjuntores e instalação de tomadas. Mais de 10 anos de experiência no mercado elétrico.'),
  (9,  'Fretes Paraná',         '98.765.432/0001-10', 'contato@fretespr.com',     '(44) 3372-2222', 'Av. Brasil, 890, Jardim Alvorada',      'Cianorte', 'PR', 'Equipe especializada em fretes urbanos, montagem simples e transporte seguro de móveis e eletrodomésticos.'),
  (10, 'TechHelp',              '55.444.333/0001-20', 'contato@techhelp.com',     '(44) 3372-3333', 'Rua Minas Gerais, 320, Vila Nova',      'Cianorte', 'PR', 'Formatação, manutenção, instalação de sistemas e suporte técnico para empresas e residências.'),
  (11, 'HidroMaster',           '11.222.333/0001-44', 'contato@hidromaster.com',  '(44) 3372-4444', 'Rua São Paulo, 78, Jardim Santa Cruz',  'Cianorte', 'PR', 'Especialistas em serviços hidráulicos residenciais e comerciais. Conserto de vazamentos, instalação de caixas d\'água e desentupimentos.'),
  (12, 'PintaBem',              '22.333.444/0001-55', 'contato@pintabem.com',     '(44) 3372-5555', 'Rua das Acácias, 210, Jardim Paulista', 'Cianorte', 'PR', 'Serviços de pintura residencial, comercial e industrial. Trabalhamos com textura, grafiato e pintura epóxi.'),
  (13, 'Limpeza Total',         '33.444.555/0001-66', 'contato@limpezatotal.com', '(44) 3372-6666', 'Rua Goiás, 55, Centro',                 'Cianorte', 'PR', 'Serviços de limpeza residencial, pós-obra e organização de ambientes. Equipe treinada e materiais de qualidade.'),
  (14, 'Jardim Vivo',           '44.555.666/0001-77', 'contato@jardimvivo.com',   '(44) 3372-7777', 'Rua das Flores, 320, Jardim Belo',      'Cianorte', 'PR', 'Jardinagem, paisagismo, corte de grama, poda de árvores e plantas. Transformamos seu jardim em um paraíso verde.'),
  (15, 'SegurMax',              '55.666.777/0001-88', 'contato@segurmax.com',     '(44) 3372-8888', 'Av. Mauá, 440, Industrial',             'Cianorte', 'PR', 'Instalação de câmeras de segurança, alarmes, cercas elétricas e controle de acesso para residências e empresas.'),
  (16, 'Saúde em Casa',         '66.777.888/0001-99', 'contato@saudeemcasa.com',  '(44) 3372-9999', 'Rua Tocantins, 180, Jardim Panorama',   'Cianorte', 'PR', 'Serviços de saúde domiciliar: enfermagem, fisioterapia, acompanhamento de idosos e cuidados pós-cirúrgicos.'),
  (17, 'Reformas PR',           '77.888.999/0001-11', 'contato@reformaspr.com',   '(44) 3372-1234', 'Rua Bahia, 95, Jardim Primavera',       'Cianorte', 'PR', 'Reformas completas, construção, demolição, reboco, azulejamento e acabamentos em geral para residências e comércios.'),
  (18, 'Ar Gelado',             '88.999.000/0001-22', 'contato@argelado.com',     '(44) 3372-5678', 'Rua Ceará, 260, Vila Industrial',       'Cianorte', 'PR', 'Instalação, manutenção e limpeza de ar condicionado split, janela e central. Atendimento rápido e garantido.'),
  (19, 'Dedetizadora Cianorte', '99.000.111/0001-33', 'contato@dede.com',         '(44) 3372-9012', 'Rua Piauí, 130, Jardim Santa Rita',     'Cianorte', 'PR', 'Dedetização, descupinização, desratização e controle de pragas urbanas. Produtos certificados e seguros.'),
  (20, 'TechMar Informática',   '11.333.555/0001-44', 'contato@techmar.com',      '(44) 3025-1111', 'Av. Colombo, 2500, Zona 03',            'Maringá',  'PR', 'Suporte técnico, formatação, redes e manutenção de computadores e notebooks para residências e empresas em Maringá.'),
  (21, 'Mudanças Fácil',        '22.444.666/0001-55', 'contato@mudancasfacil.com','(44) 3025-2222', 'Rua Pioneiro, 380, Zona 05',            'Maringá',  'PR', 'Fretes e mudanças residenciais e comerciais em Maringá e região. Equipe experiente e veículos equipados.'),
  (22, 'LimpeMax Maringá',      '33.555.777/0001-66', 'contato@limpemax.com',     '(44) 3025-3333', 'Av. Mandacaru, 1200, Zona 08',          'Maringá',  'PR', 'Limpeza residencial, comercial e pós-obra em Maringá. Diaristas treinadas e produtos de alta qualidade.');

-- SERVICOS
INSERT INTO servico (empresa_id, categoria_id, nome, descricao, preco_medio) VALUES
  -- EletroLuz (1) - Manutenção
  (1, 1, 'Instalação Elétrica',    'Instalação completa de tomadas, interruptores e quadro elétrico.', 350.00),
  (1, 1, 'Manutenção Preventiva',  'Revisão geral do sistema elétrico residencial.',                   200.00),
  (1, 1, 'Troca de Disjuntor',     'Substituição de disjuntores defeituosos ou subdimensionados.',      120.00),
  -- Fretes PR (2) - Transporte
  (2, 3, 'Frete Urbano',           'Transporte de móveis e objetos dentro da cidade.',                 150.00),
  (2, 3, 'Pequena Mudança',        'Mudança completa para residências de até 2 quartos.',              400.00),
  (2, 3, 'Entrega de Materiais',   'Entrega de materiais de construção e insumos.',                    100.00),
  -- TechHelp (3) - Tecnologia
  (3, 4, 'Formatação PC',          'Formatação e reinstalação do sistema operacional.',                  90.00),
  (3, 4, 'Suporte Técnico',        'Diagnóstico e resolução de problemas em computadores e notebooks.',  80.00),
  (3, 4, 'Instalação de Rede',     'Configuração de redes Wi-Fi e cabeadas para empresas.',            250.00),
  -- HidroMaster (4) - Manutenção
  (4, 1, 'Conserto de Vazamento',  'Localização e reparo de vazamentos em tubulações.',                180.00),
  (4, 1, 'Instalação de Caixa',    'Instalação e manutenção de caixas d\'água e reservatórios.',       300.00),
  (4, 1, 'Desentupimento',         'Desentupimento de pias, ralos, vasos e tubulações.',               150.00),
  -- PintaBem (5) - Reforma
  (5, 2, 'Pintura Residencial',    'Pintura interna e externa de residências com tinta de qualidade.', 500.00),
  (5, 2, 'Textura e Grafiato',     'Aplicação de textura e grafiato em paredes e fachadas.',           350.00),
  (5, 2, 'Pintura Comercial',      'Pintura para estabelecimentos comerciais e industriais.',           800.00),
  -- Limpeza Total (6) - Limpeza
  (6, 5, 'Diarista Residencial',   'Limpeza completa de residências com produtos inclusos.',            120.00),
  (6, 5, 'Limpeza Pós-Obra',       'Limpeza especializada após reformas e construções.',               250.00),
  (6, 5, 'Limpeza Comercial',      'Limpeza periódica de escritórios e estabelecimentos.',             200.00),
  -- Jardim Vivo (7) - Jardinagem
  (7, 6, 'Corte de Grama',         'Corte e manutenção de gramados residenciais e comerciais.',         80.00),
  (7, 6, 'Paisagismo',             'Projeto e execução de jardins e áreas verdes.',                    400.00),
  (7, 6, 'Poda de Árvores',        'Poda e remoção de árvores e arbustos.',                           200.00),
  -- SegurMax (8) - Segurança
  (8, 7, 'Instalação de Câmeras',  'Instalação de câmeras de segurança CFTV residencial e comercial.', 600.00),
  (8, 7, 'Alarme Residencial',     'Instalação de sistema de alarme com sensores e sirene.',           450.00),
  (8, 7, 'Cerca Elétrica',         'Instalação de cerca elétrica para residências e empresas.',        800.00),
  -- Saúde em Casa (9) - Saúde
  (9, 8, 'Enfermagem Domiciliar',  'Curativo, aplicação de injeção e aferição de pressão.',           150.00),
  (9, 8, 'Fisioterapia em Casa',   'Sessões de fisioterapia no conforto do lar.',                      200.00),
  (9, 8, 'Acompanhamento de Idoso','Cuidador de idoso por período integral ou parcial.',               180.00),
  -- Reformas PR (10) - Reforma
  (10, 2, 'Reforma Completa',      'Reforma geral de residências: piso, parede, teto e acabamento.',  3000.00),
  (10, 2, 'Azulejamento',          'Assentamento de azulejos e porcelanato em banheiros e cozinhas.', 400.00),
  (10, 2, 'Reboco e Massa',        'Aplicação de reboco, massa corrida e preparação de paredes.',      300.00),
  -- Ar Gelado (11) - Manutenção
  (11, 1, 'Instalação de Split',   'Instalação completa de ar condicionado split.',                    350.00),
  (11, 1, 'Limpeza de Ar',         'Higienização e limpeza de ar condicionado split e janela.',        120.00),
  (11, 1, 'Manutenção Preventiva', 'Revisão e manutenção preventiva do sistema de ar condicionado.',   180.00),
  -- Dedetizadora (12) - Limpeza
  (12, 5, 'Dedetização',           'Controle de baratas, formigas e insetos em geral.',                200.00),
  (12, 5, 'Descupinização',        'Eliminação de cupins em móveis, estruturas e pisos.',              350.00),
  (12, 5, 'Desratização',          'Controle e eliminação de ratos e roedores.',                       280.00),
  -- TechMar Maringá (13) - Tecnologia
  (13, 4, 'Formatação e Backup',   'Formatação com backup de dados e reinstalação de programas.',       100.00),
  (13, 4, 'Rede Empresarial',      'Instalação e configuração de redes para empresas.',                400.00),
  (13, 4, 'Recuperação de Dados',  'Recuperação de arquivos em HD e pen drive.',                       250.00),
  -- Mudanças Fácil Maringá (14) - Transporte
  (14, 3, 'Mudança Residencial',   'Mudança completa para residências em Maringá e região.',           600.00),
  (14, 3, 'Frete Executivo',       'Transporte rápido e seguro de itens frágeis e valiosos.',          200.00),
  (14, 3, 'Mudança Comercial',     'Mudança de escritórios e estabelecimentos comerciais.',            1200.00),
  -- LimpeMax Maringá (15) - Limpeza
  (15, 5, 'Diarista Maringá',      'Limpeza doméstica completa com produtos inclusos.',                130.00),
  (15, 5, 'Limpeza Pós-Festa',     'Limpeza após eventos e festas.',                                  300.00),
  (15, 5, 'Limpeza de Vidros',     'Limpeza e polimento de vidros e fachadas.',                        180.00);

-- FUNCIONARIOS
INSERT INTO funcionario (empresa_id, nome, telefone, cargo) VALUES
  (1, 'Carlos Eletricista',   '(44) 99900-0001', 'Eletricista Sênior'),
  (1, 'Roberto Silva',        '(44) 99900-0002', 'Auxiliar Elétrico'),
  (2, 'Marcos Motorista',     '(44) 99900-0003', 'Motorista'),
  (2, 'Paulo Ajudante',       '(44) 99900-0004', 'Ajudante de Frete'),
  (3, 'Lucas TI',             '(44) 99900-0005', 'Técnico em Informática'),
  (4, 'José Encanador',       '(44) 99900-0006', 'Encanador'),
  (5, 'André Pintor',         '(44) 99900-0007', 'Pintor'),
  (6, 'Mariana Limpeza',      '(44) 99900-0008', 'Diarista'),
  (7, 'Fernando Jardineiro',  '(44) 99900-0009', 'Jardineiro'),
  (8, 'Ricardo Segurança',    '(44) 99900-0010', 'Técnico em Segurança'),
  (9, 'Dra. Patrícia Enfermeira', '(44) 99900-0011', 'Enfermeira'),
  (10, 'Gilberto Pedreiro',   '(44) 99900-0012', 'Pedreiro'),
  (11, 'Rogério Técnico',     '(44) 99900-0013', 'Técnico em Refrigeração'),
  (12, 'Sandro Dedetizador',  '(44) 99900-0014', 'Dedetizador'),
  (13, 'Bruno Técnico',       '(44) 99900-0015', 'Técnico em TI'),
  (14, 'Wendell Motorista',   '(44) 99900-0016', 'Motorista'),
  (15, 'Juliana Limpeza',     '(44) 99900-0017', 'Diarista');

-- SOLICITACOES
INSERT INTO solicitacao (usuario_id, empresa_id, descricao, prioridade, status, resposta, funcionario_id, data_conclusao) VALUES
  (2, 1, 'Preciso instalar 4 tomadas novas no quarto e sala.', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho. Serviço realizado com sucesso!', 1, '2026-05-20'),
  (2, 3, 'Meu notebook não liga mais, preciso de diagnóstico.', 'Alta', 'Em andamento', 'Olá! Recebemos sua solicitação e já estamos a caminho. Diagnóstico: fonte danificada. Aguardando peça.', 5, NULL),
  (3, 4, 'Torneira da cozinha com vazamento há 3 dias.', 'Alta', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho. Torneira trocada e vazamento resolvido.', 6, '2026-05-22'),
  (4, 5, 'Preciso pintar a sala e dois quartos.', 'Baixa', 'Pendente', NULL, NULL, NULL),
  (5, 7, 'Jardim precisando de corte de grama e poda.', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho. Jardim limpo e podado com sucesso!', 7, '2026-05-25'),
  (6, 13, 'Computador lento e com vírus.', 'Média', 'Em andamento', 'Olá! Recebemos sua solicitação e já estamos a caminho. Computador em processo de formatação.', 15, NULL);

-- AVALIACOES
INSERT INTO avaliacao (usuario_id, empresa_id, solicitacao_id, nota, comentario) VALUES
  (2, 1, 1, 5, 'Serviço excelente! Profissional pontual e muito cuidadoso. Recomendo!'),
  (3, 4, 3, 4, 'Resolveu rápido o problema. Ficou muito bom, só demorou um pouco para chegar.'),
  (5, 7, 5, 5, 'Jardim ficou lindo! Profissional dedicado e caprichoso. Com certeza contratarei novamente.');