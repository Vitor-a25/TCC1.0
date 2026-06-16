-- OPUS | Backup automático gerado em 17/06/2026 00:43:22
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;

-- Tabela: avaliacao
DROP TABLE IF EXISTS `avaliacao`;
CREATE TABLE `avaliacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `solicitacao_id` int(11) DEFAULT NULL,
  `nota` int(11) NOT NULL CHECK (`nota` >= 1 and `nota` <= 5),
  `comentario` text DEFAULT NULL,
  `moderado` tinyint(1) DEFAULT 0,
  `data` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `solicitacao_id` (`solicitacao_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `avaliacao_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  CONSTRAINT `avaliacao_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `avaliacao_ibfk_3` FOREIGN KEY (`solicitacao_id`) REFERENCES `solicitacao` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `avaliacao` VALUES
('4', '10', '3', '17', '5', '', '0', '2026-06-03 22:22:57'),
('5', '4', '3', '16', '1', '', '0', '2026-06-03 22:24:02'),
('6', '4', '3', '15', '1', '', '0', '2026-06-03 22:24:04'),
('7', '4', '3', '14', '1', '', '0', '2026-06-03 22:24:05'),
('8', '4', '3', '13', '1', '', '0', '2026-06-03 22:24:07'),
('9', '4', '2', '12', '5', '', '0', '2026-06-03 22:24:08'),
('10', '4', '2', '11', '5', '', '0', '2026-06-03 22:24:10'),
('11', '4', '2', '10', '5', '', '0', '2026-06-03 22:24:11'),
('12', '4', '1', '9', '3', '', '0', '2026-06-03 22:24:12'),
('13', '4', '1', '8', '3', '', '0', '2026-06-03 22:24:14'),
('14', '4', '1', '7', '3', '', '0', '2026-06-03 22:24:15'),
('15', '10', '3', '21', '4', '', '0', '2026-06-03 23:33:26'),
('16', '10', '3', '19', '2', '', '0', '2026-06-03 23:33:29'),
('17', '10', '3', '18', '3', '', '0', '2026-06-03 23:33:31');

-- Tabela: backup_log
DROP TABLE IF EXISTS `backup_log`;
CREATE TABLE `backup_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `arquivo` varchar(255) DEFAULT NULL,
  `tamanho` int(11) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `backup_log` VALUES
('1', 'opus_backup_20260604_025322.sql', '23909', '2026-06-03 21:53:22'),
('2', 'opus_backup_20260616_001538.sql', '27639', '2026-06-15 19:15:38');

-- Tabela: categoria
DROP TABLE IF EXISTS `categoria`;
CREATE TABLE `categoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `icone` varchar(10) DEFAULT '?',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categoria` VALUES
('1', 'Manutenção', '🔧'),
('2', 'Reforma', '🏠'),
('3', 'Transporte', '🚚'),
('4', 'Tecnologia', '💻'),
('5', 'Limpeza', '🧹'),
('6', 'Jardinagem', '🌿'),
('7', 'Segurança', '🔒'),
('8', 'Saúde', '🏥');

-- Tabela: empresa
DROP TABLE IF EXISTS `empresa`;
CREATE TABLE `empresa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `nome` varchar(100) NOT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` varchar(150) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  UNIQUE KEY `cnpj` (`cnpj`),
  CONSTRAINT `empresa_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `empresa` VALUES
('1', '8', 'EletroLuz Cianorte', '12.345.678/0001-90', 'contato@eletroluz.com', '(44) 3372-1111', 'Rua Paraná, 145, Centro', 'Cianorte', 'PR', 'Atendimento residencial e comercial, manutenção preventiva, troca de disjuntores e instalação de tomadas. Mais de 10 anos de experiência no mercado elétrico.', '1', '2026-06-02 22:43:02'),
('2', '9', 'Fretes Paraná', '98.765.432/0001-10', 'contato@fretespr.com', '(44) 3372-2222', 'Av. Brasil, 890, Jardim Alvorada', 'Cianorte', 'PR', 'Equipe especializada em fretes urbanos, montagem simples e transporte seguro de móveis e eletrodomésticos.', '1', '2026-06-02 22:43:02'),
('3', '10', 'TechHelp', '55.444.333/0001-20', 'contato@techhelp.com', '(44) 3372-3333', 'Rua Minas Gerais, 320, Vila Nova', 'Cianorte', 'PR', 'Formatação, manutenção, instalação de sistemas e suporte técnico para empresas e residências.', '1', '2026-06-02 22:43:02'),
('4', '11', 'HidroMaster', '11.222.333/0001-44', 'contato@hidromaster.com', '(44) 3372-4444', 'Rua São Paulo, 78, Jardim Santa Cruz', 'Cianorte', 'PR', 'Especialistas em serviços hidráulicos residenciais e comerciais. Conserto de vazamentos, instalação de caixas d\'água e desentupimentos.', '1', '2026-06-02 22:43:02'),
('5', '12', 'PintaBem', '22.333.444/0001-55', 'contato@pintabem.com', '(44) 3372-5555', 'Rua das Acácias, 210, Jardim Paulista', 'Cianorte', 'PR', 'Serviços de pintura residencial, comercial e industrial. Trabalhamos com textura, grafiato e pintura epóxi.', '1', '2026-06-02 22:43:02'),
('6', '13', 'Limpeza Total', '33.444.555/0001-66', 'contato@limpezatotal.com', '(44) 3372-6666', 'Rua Goiás, 55, Centro', 'Cianorte', 'PR', 'Serviços de limpeza residencial, pós-obra e organização de ambientes. Equipe treinada e materiais de qualidade.', '1', '2026-06-02 22:43:02'),
('7', '14', 'Jardim Vivo', '44.555.666/0001-77', 'contato@jardimvivo.com', '(44) 3372-7777', 'Rua das Flores, 320, Jardim Belo', 'Cianorte', 'PR', 'Jardinagem, paisagismo, corte de grama, poda de árvores e plantas. Transformamos seu jardim em um paraíso verde.', '1', '2026-06-02 22:43:02'),
('8', '15', 'SegurMax', '55.666.777/0001-88', 'contato@segurmax.com', '(44) 3372-8888', 'Av. Mauá, 440, Industrial', 'Cianorte', 'PR', 'Instalação de câmeras de segurança, alarmes, cercas elétricas e controle de acesso para residências e empresas.', '1', '2026-06-02 22:43:02'),
('9', '16', 'Saúde em Casa', '66.777.888/0001-99', 'contato@saudeemcasa.com', '(44) 3372-9999', 'Rua Tocantins, 180, Jardim Panorama', 'Cianorte', 'PR', 'Serviços de saúde domiciliar: enfermagem, fisioterapia, acompanhamento de idosos e cuidados pós-cirúrgicos.', '1', '2026-06-02 22:43:02'),
('10', '17', 'Reformas PR', '77.888.999/0001-11', 'contato@reformaspr.com', '(44) 3372-1234', 'Rua Bahia, 95, Jardim Primavera', 'Cianorte', 'PR', 'Reformas completas, construção, demolição, reboco, azulejamento e acabamentos em geral para residências e comércios.', '1', '2026-06-02 22:43:02'),
('11', '18', 'Ar Gelado', '88.999.000/0001-22', 'contato@argelado.com', '(44) 3372-5678', 'Rua Ceará, 260, Vila Industrial', 'Cianorte', 'PR', 'Instalação, manutenção e limpeza de ar condicionado split, janela e central. Atendimento rápido e garantido.', '1', '2026-06-02 22:43:02'),
('12', '19', 'Dedetizadora Cianorte', '99.000.111/0001-33', 'contato@dede.com', '(44) 3372-9012', 'Rua Piauí, 130, Jardim Santa Rita', 'Cianorte', 'PR', 'Dedetização, descupinização, desratização e controle de pragas urbanas. Produtos certificados e seguros.', '1', '2026-06-02 22:43:02'),
('13', '20', 'TechMar Informática', '11.333.555/0001-44', 'contato@techmar.com', '(44) 3025-1111', 'Av. Colombo, 2500, Zona 03', 'Maringá', 'PR', 'Suporte técnico, formatação, redes e manutenção de computadores e notebooks para residências e empresas em Maringá.', '1', '2026-06-02 22:43:02'),
('14', '21', 'Mudanças Fácil', '22.444.666/0001-55', 'contato@mudancasfacil.com', '(44) 3025-2222', 'Rua Pioneiro, 380, Zona 05', 'Maringá', 'PR', 'Fretes e mudanças residenciais e comerciais em Maringá e região. Equipe experiente e veículos equipados.', '1', '2026-06-02 22:43:02'),
('15', '22', 'LimpeMax Maringá', '33.555.777/0001-66', 'contato@limpemax.com', '(44) 3025-3333', 'Av. Mandacaru, 1200, Zona 08', 'Maringá', 'PR', 'Limpeza residencial, comercial e pós-obra em Maringá. Diaristas treinadas e produtos de alta qualidade.', '1', '2026-06-02 22:43:02');

-- Tabela: funcionario
DROP TABLE IF EXISTS `funcionario`;
CREATE TABLE `funcionario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `funcionario_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `funcionario` VALUES
('1', '1', 'Carlos Eletricista', '(44) 99900-0001', 'Eletricista Sênior', '1', '2026-06-02 22:43:02'),
('2', '1', 'Roberto Silva', '(44) 99900-0002', 'Auxiliar Elétrico', '1', '2026-06-02 22:43:02'),
('3', '2', 'Marcos Motorista', '(44) 99900-0003', 'Motorista', '1', '2026-06-02 22:43:02'),
('4', '2', 'Paulo Ajudante', '(44) 99900-0004', 'Ajudante de Frete', '1', '2026-06-02 22:43:02'),
('5', '3', 'Lucas TI', '(44) 99900-0005', 'Técnico em Informática', '1', '2026-06-02 22:43:02'),
('6', '4', 'José Encanador', '(44) 99900-0006', 'Encanador', '1', '2026-06-02 22:43:02'),
('7', '5', 'André Pintor', '(44) 99900-0007', 'Pintor', '1', '2026-06-02 22:43:02'),
('8', '6', 'Mariana Limpeza', '(44) 99900-0008', 'Diarista', '1', '2026-06-02 22:43:02'),
('9', '7', 'Fernando Jardineiro', '(44) 99900-0009', 'Jardineiro', '1', '2026-06-02 22:43:02'),
('10', '8', 'Ricardo Segurança', '(44) 99900-0010', 'Técnico em Segurança', '1', '2026-06-02 22:43:02'),
('11', '9', 'Dra. Patrícia Enfermeira', '(44) 99900-0011', 'Enfermeira', '1', '2026-06-02 22:43:02'),
('12', '10', 'Gilberto Pedreiro', '(44) 99900-0012', 'Pedreiro', '1', '2026-06-02 22:43:02'),
('13', '11', 'Rogério Técnico', '(44) 99900-0013', 'Técnico em Refrigeração', '1', '2026-06-02 22:43:02'),
('14', '12', 'Sandro Dedetizador', '(44) 99900-0014', 'Dedetizador', '1', '2026-06-02 22:43:02'),
('15', '13', 'Bruno Técnico', '(44) 99900-0015', 'Técnico em TI', '1', '2026-06-02 22:43:02'),
('16', '14', 'Wendell Motorista', '(44) 99900-0016', 'Motorista', '1', '2026-06-02 22:43:02'),
('17', '15', 'Juliana Limpeza', '(44) 99900-0017', 'Diarista', '1', '2026-06-02 22:43:02');

-- Tabela: servico
DROP TABLE IF EXISTS `servico`;
CREATE TABLE `servico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco_medio` decimal(15,2) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `empresa_id` (`empresa_id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `servico_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `servico_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `servico` VALUES
('1', '1', '1', 'Instalação Elétrica', 'Instalação completa de tomadas, interruptores e quadro elétrico.', '350.00', '1', '2026-06-02 22:43:02'),
('2', '1', '1', 'Manutenção Preventiva', 'Revisão geral do sistema elétrico residencial.', '200.00', '1', '2026-06-02 22:43:02'),
('3', '1', '1', 'Troca de Disjuntor', 'Substituição de disjuntores defeituosos ou subdimensionados.', '120.00', '1', '2026-06-02 22:43:02'),
('4', '2', '3', 'Frete Urbano', 'Transporte de móveis e objetos dentro da cidade.', '150.00', '1', '2026-06-02 22:43:02'),
('5', '2', '3', 'Pequena Mudança', 'Mudança completa para residências de até 2 quartos.', '400.00', '1', '2026-06-02 22:43:02'),
('6', '2', '3', 'Entrega de Materiais', 'Entrega de materiais de construção e insumos.', '100.00', '1', '2026-06-02 22:43:02'),
('7', '3', '4', 'Formatação PC', 'Formatação e reinstalação do sistema operacional.', '90.00', '1', '2026-06-02 22:43:02'),
('8', '3', '4', 'Suporte Técnico', 'Diagnóstico e resolução de problemas em computadores e notebooks.', '80.00', '1', '2026-06-02 22:43:02'),
('9', '3', '4', 'Instalação de Rede', 'Configuração de redes Wi-Fi e cabeadas para empresas.', '250.00', '1', '2026-06-02 22:43:02'),
('10', '4', '1', 'Conserto de Vazamento', 'Localização e reparo de vazamentos em tubulações.', '180.00', '1', '2026-06-02 22:43:02'),
('11', '4', '1', 'Instalação de Caixa', 'Instalação e manutenção de caixas d\'água e reservatórios.', '300.00', '1', '2026-06-02 22:43:02'),
('12', '4', '1', 'Desentupimento', 'Desentupimento de pias, ralos, vasos e tubulações.', '150.00', '1', '2026-06-02 22:43:02'),
('13', '5', '2', 'Pintura Residencial', 'Pintura interna e externa de residências com tinta de qualidade.', '500.00', '1', '2026-06-02 22:43:02'),
('14', '5', '2', 'Textura e Grafiato', 'Aplicação de textura e grafiato em paredes e fachadas.', '350.00', '1', '2026-06-02 22:43:02'),
('15', '5', '2', 'Pintura Comercial', 'Pintura para estabelecimentos comerciais e industriais.', '800.00', '1', '2026-06-02 22:43:02'),
('16', '6', '5', 'Diarista Residencial', 'Limpeza completa de residências com produtos inclusos.', '120.00', '1', '2026-06-02 22:43:02'),
('17', '6', '5', 'Limpeza Pós-Obra', 'Limpeza especializada após reformas e construções.', '250.00', '1', '2026-06-02 22:43:02'),
('18', '6', '5', 'Limpeza Comercial', 'Limpeza periódica de escritórios e estabelecimentos.', '200.00', '1', '2026-06-02 22:43:02'),
('19', '7', '6', 'Corte de Grama', 'Corte e manutenção de gramados residenciais e comerciais.', '80.00', '1', '2026-06-02 22:43:02'),
('20', '7', '6', 'Paisagismo', 'Projeto e execução de jardins e áreas verdes.', '400.00', '1', '2026-06-02 22:43:02'),
('21', '7', '6', 'Poda de Árvores', 'Poda e remoção de árvores e arbustos.', '200.00', '1', '2026-06-02 22:43:02'),
('22', '8', '7', 'Instalação de Câmeras', 'Instalação de câmeras de segurança CFTV residencial e comercial.', '600.00', '1', '2026-06-02 22:43:02'),
('23', '8', '7', 'Alarme Residencial', 'Instalação de sistema de alarme com sensores e sirene.', '450.00', '1', '2026-06-02 22:43:02'),
('24', '8', '7', 'Cerca Elétrica', 'Instalação de cerca elétrica para residências e empresas.', '800.00', '1', '2026-06-02 22:43:02'),
('25', '9', '8', 'Enfermagem Domiciliar', 'Curativo, aplicação de injeção e aferição de pressão.', '150.00', '1', '2026-06-02 22:43:02'),
('26', '9', '8', 'Fisioterapia em Casa', 'Sessões de fisioterapia no conforto do lar.', '200.00', '1', '2026-06-02 22:43:02'),
('27', '9', '8', 'Acompanhamento de Idoso', 'Cuidador de idoso por período integral ou parcial.', '180.00', '1', '2026-06-02 22:43:02'),
('28', '10', '2', 'Reforma Completa', 'Reforma geral de residências: piso, parede, teto e acabamento.', '3000.00', '1', '2026-06-02 22:43:02'),
('29', '10', '2', 'Azulejamento', 'Assentamento de azulejos e porcelanato em banheiros e cozinhas.', '400.00', '1', '2026-06-02 22:43:02'),
('30', '10', '2', 'Reboco e Massa', 'Aplicação de reboco, massa corrida e preparação de paredes.', '300.00', '1', '2026-06-02 22:43:02'),
('31', '11', '1', 'Instalação de Split', 'Instalação completa de ar condicionado split.', '350.00', '1', '2026-06-02 22:43:02'),
('32', '11', '1', 'Limpeza de Ar', 'Higienização e limpeza de ar condicionado split e janela.', '120.00', '1', '2026-06-02 22:43:02'),
('33', '11', '1', 'Manutenção Preventiva', 'Revisão e manutenção preventiva do sistema de ar condicionado.', '180.00', '1', '2026-06-02 22:43:02'),
('34', '12', '5', 'Dedetização', 'Controle de baratas, formigas e insetos em geral.', '200.00', '1', '2026-06-02 22:43:02'),
('35', '12', '5', 'Descupinização', 'Eliminação de cupins em móveis, estruturas e pisos.', '350.00', '1', '2026-06-02 22:43:02'),
('36', '12', '5', 'Desratização', 'Controle e eliminação de ratos e roedores.', '280.00', '1', '2026-06-02 22:43:02'),
('37', '13', '4', 'Formatação e Backup', 'Formatação com backup de dados e reinstalação de programas.', '100.00', '1', '2026-06-02 22:43:02'),
('38', '13', '4', 'Rede Empresarial', 'Instalação e configuração de redes para empresas.', '400.00', '1', '2026-06-02 22:43:02'),
('39', '13', '4', 'Recuperação de Dados', 'Recuperação de arquivos em HD e pen drive.', '250.00', '1', '2026-06-02 22:43:02'),
('40', '14', '3', 'Mudança Residencial', 'Mudança completa para residências em Maringá e região.', '600.00', '1', '2026-06-02 22:43:02'),
('41', '14', '3', 'Frete Executivo', 'Transporte rápido e seguro de itens frágeis e valiosos.', '200.00', '1', '2026-06-02 22:43:02'),
('42', '14', '3', 'Mudança Comercial', 'Mudança de escritórios e estabelecimentos comerciais.', '1200.00', '1', '2026-06-02 22:43:02'),
('43', '15', '5', 'Diarista Maringá', 'Limpeza doméstica completa com produtos inclusos.', '130.00', '1', '2026-06-02 22:43:02'),
('44', '15', '5', 'Limpeza Pós-Festa', 'Limpeza após eventos e festas.', '300.00', '1', '2026-06-02 22:43:02'),
('45', '15', '5', 'Limpeza de Vidros', 'Limpeza e polimento de vidros e fachadas.', '180.00', '1', '2026-06-02 22:43:02');

-- Tabela: solicitacao
DROP TABLE IF EXISTS `solicitacao`;
CREATE TABLE `solicitacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `descricao` text NOT NULL,
  `prioridade` enum('Baixa','Média','Alta') DEFAULT 'Média',
  `status` enum('Pendente','Em andamento','Concluído','Cancelado') DEFAULT 'Pendente',
  `resposta` text DEFAULT NULL,
  `data_solicitacao` datetime DEFAULT current_timestamp(),
  `data_conclusao` date DEFAULT NULL,
  `funcionario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `empresa_id` (`empresa_id`),
  KEY `funcionario_id` (`funcionario_id`),
  CONSTRAINT `solicitacao_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  CONSTRAINT `solicitacao_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `solicitacao_ibfk_3` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionario` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `solicitacao` VALUES
('1', '2', '1', 'Preciso instalar 4 tomadas novas no quarto e sala.', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho. Serviço realizado com sucesso!', '2026-06-02 22:43:02', '2026-05-20', '1'),
('2', '2', '3', 'Meu notebook não liga mais, preciso de diagnóstico.', 'Alta', 'Em andamento', 'Olá! Recebemos sua solicitação e já estamos a caminho. Diagnóstico: fonte danificada. Aguardando peça.', '2026-06-02 22:43:02', NULL, '5'),
('3', '3', '4', 'Torneira da cozinha com vazamento há 3 dias.', 'Alta', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho. Torneira trocada e vazamento resolvido.', '2026-06-02 22:43:02', '2026-05-22', '6'),
('4', '4', '5', 'Preciso pintar a sala e dois quartos.', 'Baixa', 'Pendente', NULL, '2026-06-02 22:43:02', NULL, NULL),
('5', '5', '7', 'Jardim precisando de corte de grama e poda.', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho. Jardim limpo e podado com sucesso!', '2026-06-02 22:43:02', '2026-05-25', '7'),
('6', '6', '13', 'Computador lento e com vírus.', 'Média', 'Em andamento', 'Olá! Recebemos sua solicitação e já estamos a caminho. Computador em processo de formatação.', '2026-06-02 22:43:02', NULL, '15'),
('7', '4', '1', 'Serviços: Troca de Disjuntor', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 21:59:55', '2026-06-04', '1'),
('8', '4', '1', 'Serviços: Instalação Elétrica', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 22:00:00', '2026-06-04', '1'),
('9', '4', '1', 'Serviços: Instalação Elétrica', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 22:00:08', '2026-06-04', '1'),
('10', '4', '2', 'Serviços: Frete Urbano', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 22:00:11', '2026-06-04', '3'),
('11', '4', '2', 'Serviços: Frete Urbano', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 22:00:14', '2026-06-04', '4'),
('12', '4', '2', 'Serviços: Frete Urbano', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 22:00:19', '2026-06-04', '3'),
('13', '4', '3', 'Serviços: Instalação de Rede', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 22:04:05', '2026-06-04', '5'),
('14', '4', '3', 'Serviços: Instalação de Rede', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 22:04:08', '2026-06-04', '5'),
('15', '4', '3', 'Serviços: Instalação de Rede', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 22:04:11', '2026-06-04', '5'),
('16', '4', '3', 'Serviços: Instalação de Rede', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 22:04:14', '2026-06-04', '5'),
('17', '10', '3', 'Serviços: Instalação de Rede', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 22:20:08', '2026-06-04', '5'),
('18', '10', '3', 'Serviços: Formatação PC', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 23:32:22', '2026-06-04', '5'),
('19', '10', '3', 'Serviços: Suporte Técnico', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 23:32:27', '2026-06-04', '5'),
('20', '10', '1', 'Serviços: Instalação Elétrica', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 23:32:30', '2026-06-04', '1'),
('21', '10', '3', 'Serviços: Formatação PC, Suporte Técnico, Instalação de Rede', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho.', '2026-06-03 23:32:34', '2026-06-04', '5');

-- Tabela: usuario
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` varchar(150) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `tipo` enum('cliente','empresa','admin') DEFAULT 'cliente',
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuario` VALUES
('1', 'Administrador', 'admin@opus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'admin', '2026-06-02 22:43:02'),
('2', 'João Silva', 'joao@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-1111', 'Rua das Acácias, 145, Jardim Paulista', 'Cianorte', 'PR', 'cliente', '2026-06-02 22:43:02'),
('3', 'Maria Souza', 'maria@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-2222', 'Rua Paraná, 320, Centro', 'Cianorte', 'PR', 'cliente', '2026-06-02 22:43:02'),
('4', 'Carlos Oliveira', 'carlos@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-3333', 'Av. Brasil, 890, Jardim Alvorada', 'Cianorte', 'PR', 'cliente', '2026-06-02 22:43:02'),
('5', 'Ana Paula Lima', 'ana@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-4444', 'Rua Minas Gerais, 210, Vila Nova', 'Cianorte', 'PR', 'cliente', '2026-06-02 22:43:02'),
('6', 'Pedro Almeida', 'pedro@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-5555', 'Rua São Paulo, 78, Jardim Santa Cruz', 'Maringá', 'PR', 'cliente', '2026-06-02 22:43:02'),
('7', 'Fernanda Costa', 'fernanda@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-6666', 'Av. Colombo, 440, Zona 07', 'Maringá', 'PR', 'cliente', '2026-06-02 22:43:02'),
('8', 'EletroLuz Resp', 'eletroluz@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1001', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-02 22:43:02'),
('9', 'Fretes PR Resp', 'fretes@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1002', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-02 22:43:02'),
('10', 'TechHelp Resp', 'techhelp@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1003', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-02 22:43:02'),
('11', 'HidroMaster Resp', 'hidro@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1004', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-02 22:43:02'),
('12', 'PintaBem Resp', 'pintabem@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1005', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-02 22:43:02'),
('13', 'LimpezaTotal Resp', 'limpeza@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1006', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-02 22:43:02'),
('14', 'JardimVivo Resp', 'jardim@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1007', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-02 22:43:02'),
('15', 'SegurMax Resp', 'segurmax@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1008', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-02 22:43:02'),
('16', 'SaúdeEmCasa Resp', 'saude@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1009', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-02 22:43:02'),
('17', 'ReformasPR Resp', 'reformas@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1010', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-02 22:43:02'),
('18', 'ArGelado Resp', 'argelado@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1011', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-02 22:43:02'),
('19', 'DedetizadoraCia Resp', 'dedetizadora@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1012', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-02 22:43:02'),
('20', 'TechMar Resp', 'techmar@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1013', NULL, 'Maringá', 'PR', 'empresa', '2026-06-02 22:43:02'),
('21', 'MudançasFácil Resp', 'mudancas@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1014', NULL, 'Maringá', 'PR', 'empresa', '2026-06-02 22:43:02'),
('22', 'LimpeMax Resp', 'limpemax@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1015', NULL, 'Maringá', 'PR', 'empresa', '2026-06-02 22:43:02');

SET FOREIGN_KEY_CHECKS=1;
