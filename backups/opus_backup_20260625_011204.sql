-- OPUS | Backup automático gerado em 25/06/2026 01:12:04
SET FOREIGN_KEY_CHECKS=0;

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `avaliacao` VALUES
('1', '2', '1', '1', '5', 'Serviço excelente! Profissional pontual e muito cuidadoso. Recomendo!', '0', '2026-06-22 19:24:33'),
('2', '3', '4', '3', '4', 'Resolveu rápido o problema. Ficou muito bom, só demorou um pouco para chegar.', '0', '2026-06-22 19:24:33'),
('3', '5', '7', '5', '5', 'Jardim ficou lindo! Profissional dedicado e caprichoso. Com certeza contratarei novamente.', '0', '2026-06-22 19:24:33'),
('4', '5', '1', '7', '5', '', '0', '2026-06-22 19:57:11'),
('5', '5', '1', '9', '5', '', '0', '2026-06-22 21:17:17'),
('6', '8', '1', '10', '5', '', '0', '2026-06-22 22:52:20');

DROP TABLE IF EXISTS `backup_log`;
CREATE TABLE `backup_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `arquivo` varchar(255) DEFAULT NULL,
  `tamanho` int(11) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `backup_log` VALUES
('1', 'opus_backup_20260623_002609.sql', '24357', '2026-06-22 19:26:09');

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
('1', '8', 'EletroLuz Cianorte', '12.345.678/0001-90', 'contato@eletroluz.com', '(44) 3372-1111', 'Rua Paraná, 145, Centro', 'Cianorte', 'PR', 'Atendimento residencial e comercial, manutenção preventiva, troca de disjuntores e instalação de tomadas. Mais de 10 anos de experiência no mercado elétrico.', '1', '2026-06-22 19:24:33'),
('2', '9', 'Fretes Paraná', '98.765.432/0001-10', 'contato@fretespr.com', '(44) 3372-2222', 'Av. Brasil, 890, Jardim Alvorada', 'Cianorte', 'PR', 'Equipe especializada em fretes urbanos, montagem simples e transporte seguro de móveis e eletrodomésticos.', '1', '2026-06-22 19:24:33'),
('3', '10', 'TechHelp', '55.444.333/0001-20', 'contato@techhelp.com', '(44) 3372-3333', 'Rua Minas Gerais, 320, Vila Nova', 'Cianorte', 'PR', 'Formatação, manutenção, instalação de sistemas e suporte técnico para empresas e residências.', '1', '2026-06-22 19:24:33'),
('4', '11', 'HidroMaster', '11.222.333/0001-44', 'contato@hidromaster.com', '(44) 3372-4444', 'Rua São Paulo, 78, Jardim Santa Cruz', 'Cianorte', 'PR', 'Especialistas em serviços hidráulicos residenciais e comerciais. Conserto de vazamentos, instalação de caixas d\'água e desentupimentos.', '1', '2026-06-22 19:24:33'),
('5', '12', 'PintaBem', '22.333.444/0001-55', 'contato@pintabem.com', '(44) 3372-5555', 'Rua das Acácias, 210, Jardim Paulista', 'Cianorte', 'PR', 'Serviços de pintura residencial, comercial e industrial. Trabalhamos com textura, grafiato e pintura epóxi.', '1', '2026-06-22 19:24:33'),
('6', '13', 'Limpeza Total', '33.444.555/0001-66', 'contato@limpezatotal.com', '(44) 3372-6666', 'Rua Goiás, 55, Centro', 'Cianorte', 'PR', 'Serviços de limpeza residencial, pós-obra e organização de ambientes. Equipe treinada e materiais de qualidade.', '1', '2026-06-22 19:24:33'),
('7', '14', 'Jardim Vivo', '44.555.666/0001-77', 'contato@jardimvivo.com', '(44) 3372-7777', 'Rua das Flores, 320, Jardim Belo', 'Cianorte', 'PR', 'Jardinagem, paisagismo, corte de grama, poda de árvores e plantas. Transformamos seu jardim em um paraíso verde.', '1', '2026-06-22 19:24:33'),
('8', '15', 'SegurMax', '55.666.777/0001-88', 'contato@segurmax.com', '(44) 3372-8888', 'Av. Mauá, 440, Industrial', 'Cianorte', 'PR', 'Instalação de câmeras de segurança, alarmes, cercas elétricas e controle de acesso para residências e empresas.', '1', '2026-06-22 19:24:33'),
('9', '16', 'Saúde em Casa', '66.777.888/0001-99', 'contato@saudeemcasa.com', '(44) 3372-9999', 'Rua Tocantins, 180, Jardim Panorama', 'Cianorte', 'PR', 'Serviços de saúde domiciliar: enfermagem, fisioterapia, acompanhamento de idosos e cuidados pós-cirúrgicos.', '1', '2026-06-22 19:24:33'),
('10', '17', 'Reformas PR', '77.888.999/0001-11', 'contato@reformaspr.com', '(44) 3372-1234', 'Rua Bahia, 95, Jardim Primavera', 'Cianorte', 'PR', 'Reformas completas, construção, demolição, reboco, azulejamento e acabamentos em geral para residências e comércios.', '1', '2026-06-22 19:24:33'),
('11', '18', 'Ar Gelado', '88.999.000/0001-22', 'contato@argelado.com', '(44) 3372-5678', 'Rua Ceará, 260, Vila Industrial', 'Cianorte', 'PR', 'Instalação, manutenção e limpeza de ar condicionado split, janela e central. Atendimento rápido e garantido.', '1', '2026-06-22 19:24:33'),
('12', '19', 'Dedetizadora Cianorte', '99.000.111/0001-33', 'contato@dede.com', '(44) 3372-9012', 'Rua Piauí, 130, Jardim Santa Rita', 'Cianorte', 'PR', 'Dedetização, descupinização, desratização e controle de pragas urbanas. Produtos certificados e seguros.', '1', '2026-06-22 19:24:33'),
('13', '20', 'TechMar Informática', '11.333.555/0001-44', 'contato@techmar.com', '(44) 3025-1111', 'Av. Colombo, 2500, Zona 03', 'Maringá', 'PR', 'Suporte técnico, formatação, redes e manutenção de computadores e notebooks para residências e empresas em Maringá.', '1', '2026-06-22 19:24:33'),
('14', '21', 'Mudanças Fácil', '22.444.666/0001-55', 'contato@mudancasfacil.com', '(44) 3025-2222', 'Rua Pioneiro, 380, Zona 05', 'Maringá', 'PR', 'Fretes e mudanças residenciais e comerciais em Maringá e região. Equipe experiente e veículos equipados.', '1', '2026-06-22 19:24:33'),
('15', '22', 'LimpeMax Maringá', '33.555.777/0001-66', 'contato@limpemax.com', '(44) 3025-3333', 'Av. Mandacaru, 1200, Zona 08', 'Maringá', 'PR', 'Limpeza residencial, comercial e pós-obra em Maringá. Diaristas treinadas e produtos de alta qualidade.', '1', '2026-06-22 19:24:33');

DROP TABLE IF EXISTS `funcionario`;
CREATE TABLE `funcionario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `pode_atender` tinyint(1) NOT NULL DEFAULT 1,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `funcionario_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `funcionario` VALUES
('1', '1', 'Carlos Eletricista', '(44) 99900-0001', 'Eletricista Sênior', '1', '1', '2026-06-22 19:24:33'),
('2', '1', 'Roberto Silva', '(44) 99900-0002', 'Auxiliar Eletricista', '1', '1', '2026-06-22 19:24:33'),
('3', '2', 'Marcos Motorista', '(44) 99900-0003', 'Motorista', '1', '1', '2026-06-22 19:24:33'),
('4', '2', 'Paulo Ajudante', '(44) 99900-0004', 'Ajudante de Frete', '1', '1', '2026-06-22 19:24:33'),
('5', '3', 'Lucas TI', '(44) 99900-0005', 'Técnico em Informática', '1', '1', '2026-06-22 19:24:33'),
('6', '4', 'José Encanador', '(44) 99900-0006', 'Encanador', '1', '1', '2026-06-22 19:24:33'),
('7', '5', 'André Pintor', '(44) 99900-0007', 'Pintor', '1', '1', '2026-06-22 19:24:33'),
('8', '6', 'Mariana Limpeza', '(44) 99900-0008', 'Diarista', '1', '1', '2026-06-22 19:24:33'),
('9', '7', 'Fernando Jardineiro', '(44) 99900-0009', 'Jardineiro', '1', '1', '2026-06-22 19:24:33'),
('10', '8', 'Ricardo Segurança', '(44) 99900-0010', 'Técnico em Segurança', '1', '1', '2026-06-22 19:24:33'),
('11', '9', 'Dra. Patrícia Enfermeira', '(44) 99900-0011', 'Enfermeira', '1', '1', '2026-06-22 19:24:33'),
('12', '10', 'Gilberto Pedreiro', '(44) 99900-0012', 'Pedreiro', '1', '1', '2026-06-22 19:24:33'),
('13', '11', 'Rogério Técnico', '(44) 99900-0013', 'Técnico em Refrigeração', '1', '1', '2026-06-22 19:24:33'),
('14', '12', 'Sandro Dedetizador', '(44) 99900-0014', 'Dedetizador', '1', '1', '2026-06-22 19:24:33'),
('15', '13', 'Bruno Técnico', '(44) 99900-0015', 'Técnico em TI', '1', '1', '2026-06-22 19:24:33'),
('16', '14', 'Wendell Motorista', '(44) 99900-0016', 'Motorista', '1', '1', '2026-06-22 19:24:33'),
('17', '15', 'Juliana Limpeza', '(44) 99900-0017', 'Diarista', '1', '1', '2026-06-22 19:24:33');

DROP TABLE IF EXISTS `funcionario_categoria`;
CREATE TABLE `funcionario_categoria` (
  `funcionario_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  PRIMARY KEY (`funcionario_id`,`categoria_id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `funcionario_categoria_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionario` (`id`) ON DELETE CASCADE,
  CONSTRAINT `funcionario_categoria_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `funcionario_categoria` VALUES
('1', '1'),
('2', '1'),
('2', '2');

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
('1', '1', '1', 'Instalação Elétrica', 'Instalação completa de tomadas, interruptores e quadro elétrico.', '350.00', '1', '2026-06-22 19:24:33'),
('2', '1', '1', 'Manutenção Preventiva', 'Revisão geral do sistema elétrico residencial.', '200.00', '1', '2026-06-22 19:24:33'),
('3', '1', '1', 'Troca de Disjuntor', 'Substituição de disjuntores defeituosos ou subdimensionados.', '120.00', '1', '2026-06-22 19:24:33'),
('4', '2', '3', 'Frete Urbano', 'Transporte de móveis e objetos dentro da cidade.', '150.00', '1', '2026-06-22 19:24:33'),
('5', '2', '3', 'Pequena Mudança', 'Mudança completa para residências de até 2 quartos.', '400.00', '1', '2026-06-22 19:24:33'),
('6', '2', '3', 'Entrega de Materiais', 'Entrega de materiais de construção e insumos.', '100.00', '1', '2026-06-22 19:24:33'),
('7', '3', '4', 'Formatação PC', 'Formatação e reinstalação do sistema operacional.', '90.00', '1', '2026-06-22 19:24:33'),
('8', '3', '4', 'Suporte Técnico', 'Diagnóstico e resolução de problemas em computadores e notebooks.', '80.00', '1', '2026-06-22 19:24:33'),
('9', '3', '4', 'Instalação de Rede', 'Configuração de redes Wi-Fi e cabeadas para empresas.', '250.00', '1', '2026-06-22 19:24:33'),
('10', '4', '1', 'Conserto de Vazamento', 'Localização e reparo de vazamentos em tubulações.', '180.00', '1', '2026-06-22 19:24:33'),
('11', '4', '1', 'Instalação de Caixa', 'Instalação e manutenção de caixas d\'água e reservatórios.', '300.00', '1', '2026-06-22 19:24:33'),
('12', '4', '1', 'Desentupimento', 'Desentupimento de pias, ralos, vasos e tubulações.', '150.00', '1', '2026-06-22 19:24:33'),
('13', '5', '2', 'Pintura Residencial', 'Pintura interna e externa de residências com tinta de qualidade.', '500.00', '1', '2026-06-22 19:24:33'),
('14', '5', '2', 'Textura e Grafiato', 'Aplicação de textura e grafiato em paredes e fachadas.', '350.00', '1', '2026-06-22 19:24:33'),
('15', '5', '2', 'Pintura Comercial', 'Pintura para estabelecimentos comerciais e industriais.', '800.00', '1', '2026-06-22 19:24:33'),
('16', '6', '5', 'Diarista Residencial', 'Limpeza completa de residências com produtos inclusos.', '120.00', '1', '2026-06-22 19:24:33'),
('17', '6', '5', 'Limpeza Pós-Obra', 'Limpeza especializada após reformas e construções.', '250.00', '1', '2026-06-22 19:24:33'),
('18', '6', '5', 'Limpeza Comercial', 'Limpeza periódica de escritórios e estabelecimentos.', '200.00', '1', '2026-06-22 19:24:33'),
('19', '7', '6', 'Corte de Grama', 'Corte e manutenção de gramados residenciais e comerciais.', '80.00', '1', '2026-06-22 19:24:33'),
('20', '7', '6', 'Paisagismo', 'Projeto e execução de jardins e áreas verdes.', '400.00', '1', '2026-06-22 19:24:33'),
('21', '7', '6', 'Poda de Árvores', 'Poda e remoção de árvores e arbustos.', '200.00', '1', '2026-06-22 19:24:33'),
('22', '8', '7', 'Instalação de Câmeras', 'Instalação de câmeras de segurança CFTV residencial e comercial.', '600.00', '1', '2026-06-22 19:24:33'),
('23', '8', '7', 'Alarme Residencial', 'Instalação de sistema de alarme com sensores e sirene.', '450.00', '1', '2026-06-22 19:24:33'),
('24', '8', '7', 'Cerca Elétrica', 'Instalação de cerca elétrica para residências e empresas.', '800.00', '1', '2026-06-22 19:24:33'),
('25', '9', '8', 'Enfermagem Domiciliar', 'Curativo, aplicação de injeção e aferição de pressão.', '150.00', '1', '2026-06-22 19:24:33'),
('26', '9', '8', 'Fisioterapia em Casa', 'Sessões de fisioterapia no conforto do lar.', '200.00', '1', '2026-06-22 19:24:33'),
('27', '9', '8', 'Acompanhamento de Idoso', 'Cuidador de idoso por período integral ou parcial.', '180.00', '1', '2026-06-22 19:24:33'),
('28', '10', '2', 'Reforma Completa', 'Reforma geral de residências: piso, parede, teto e acabamento.', '3000.00', '1', '2026-06-22 19:24:33'),
('29', '10', '2', 'Azulejamento', 'Assentamento de azulejos e porcelanato em banheiros e cozinhas.', '400.00', '1', '2026-06-22 19:24:33'),
('30', '10', '2', 'Reboco e Massa', 'Aplicação de reboco, massa corrida e preparação de paredes.', '300.00', '1', '2026-06-22 19:24:33'),
('31', '11', '1', 'Instalação de Split', 'Instalação completa de ar condicionado split.', '350.00', '1', '2026-06-22 19:24:33'),
('32', '11', '1', 'Limpeza de Ar', 'Higienização e limpeza de ar condicionado split e janela.', '120.00', '1', '2026-06-22 19:24:33'),
('33', '11', '1', 'Manutenção Preventiva', 'Revisão e manutenção preventiva do sistema de ar condicionado.', '180.00', '1', '2026-06-22 19:24:33'),
('34', '12', '5', 'Dedetização', 'Controle de baratas, formigas e insetos em geral.', '200.00', '1', '2026-06-22 19:24:33'),
('35', '12', '5', 'Descupinização', 'Eliminação de cupins em móveis, estruturas e pisos.', '350.00', '1', '2026-06-22 19:24:33'),
('36', '12', '5', 'Desratização', 'Controle e eliminação de ratos e roedores.', '280.00', '1', '2026-06-22 19:24:33'),
('37', '13', '4', 'Formatação e Backup', 'Formatação com backup de dados e reinstalação de programas.', '100.00', '1', '2026-06-22 19:24:33'),
('38', '13', '4', 'Rede Empresarial', 'Instalação e configuração de redes para empresas.', '400.00', '1', '2026-06-22 19:24:33'),
('39', '13', '4', 'Recuperação de Dados', 'Recuperação de arquivos em HD e pen drive.', '250.00', '1', '2026-06-22 19:24:33'),
('40', '14', '3', 'Mudança Residencial', 'Mudança completa para residências em Maringá e região.', '600.00', '1', '2026-06-22 19:24:33'),
('41', '14', '3', 'Frete Executivo', 'Transporte rápido e seguro de itens frágeis e valiosos.', '200.00', '1', '2026-06-22 19:24:33'),
('42', '14', '3', 'Mudança Comercial', 'Mudança de escritórios e estabelecimentos comerciais.', '1200.00', '1', '2026-06-22 19:24:33'),
('43', '15', '5', 'Diarista Maringá', 'Limpeza doméstica completa com produtos inclusos.', '130.00', '1', '2026-06-22 19:24:33'),
('44', '15', '5', 'Limpeza Pós-Festa', 'Limpeza após eventos e festas.', '300.00', '1', '2026-06-22 19:24:33'),
('45', '15', '5', 'Limpeza de Vidros', 'Limpeza e polimento de vidros e fachadas.', '180.00', '1', '2026-06-22 19:24:33');

DROP TABLE IF EXISTS `solicitacao`;
CREATE TABLE `solicitacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `descricao` text NOT NULL,
  `prioridade` enum('Baixa','Média','Alta') DEFAULT 'Média',
  `status` enum('Pendente','Em andamento','Concluído','Cancelado','Servico Realizado') DEFAULT 'Pendente',
  `resposta` text DEFAULT NULL,
  `data_solicitacao` datetime DEFAULT current_timestamp(),
  `data_conclusao` date DEFAULT NULL,
  `funcionario_id` int(11) DEFAULT NULL,
  `tempo_deslocamento` varchar(50) DEFAULT NULL,
  `tempo_estimado` varchar(50) DEFAULT NULL,
  `inicio_previsto` varchar(10) DEFAULT NULL,
  `fora_horario` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `empresa_id` (`empresa_id`),
  KEY `funcionario_id` (`funcionario_id`),
  KEY `fk_sol_categoria` (`categoria_id`),
  CONSTRAINT `fk_sol_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON DELETE SET NULL,
  CONSTRAINT `solicitacao_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  CONSTRAINT `solicitacao_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `solicitacao_ibfk_3` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionario` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `solicitacao` VALUES
('1', '2', '1', NULL, 'Preciso instalar 4 tomadas novas no quarto e sala.', 'Média', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho. Serviço realizado com sucesso!', '2026-06-22 19:24:33', '2026-05-20', '1', NULL, NULL, NULL, '0'),
('2', '2', '3', NULL, 'Meu notebook não liga mais, preciso de diagnóstico.', 'Alta', 'Em andamento', 'Olá! Recebemos sua solicitação e já estamos a caminho. Diagnóstico: fonte danificada. Aguardando peça.', '2026-06-22 19:24:33', NULL, '5', NULL, NULL, NULL, '0'),
('3', '3', '4', NULL, 'Torneira da cozinha com vazamento há 3 dias.', 'Alta', 'Concluído', 'Olá! Recebemos sua solicitação e já estamos a caminho. Torneira trocada e vazamento resolvido.', '2026-06-22 19:24:33', '2026-05-22', '6', NULL, NULL, NULL, '0'),
('4', '4', '5', NULL, 'Preciso pintar a sala e dois quartos.', 'Baixa', 'Pendente', NULL, '2026-06-22 19:24:33', NULL, NULL, NULL, NULL, NULL, '0'),
('5', '5', '7', NULL, 'Jardim precisando de corte de grama e poda.', 'Média', 'Servico Realizado', 'Olá! Recebemos sua solicitação e já estamos a caminho. Jardim limpo e podado com sucesso!', '2026-06-22 19:24:33', '2026-06-23', '7', NULL, NULL, NULL, '0'),
('6', '6', '13', NULL, 'Computador lento e com vírus.', 'Média', 'Em andamento', 'Olá! Recebemos sua solicitação e já estamos a caminho. Computador em processo de formatação.', '2026-06-22 19:24:33', NULL, '15', NULL, NULL, NULL, '0'),
('7', '5', '1', NULL, 'Serviços: Troca de Disjuntor', 'Média', 'Servico Realizado', '', '2026-06-22 19:53:39', '2026-06-23', '1', '1 hora', '2 hora', '', '0'),
('8', '5', '1', '1', 'Serviços: Instalação Elétrica', 'Média', 'Cancelado', '', '2026-06-22 20:09:29', NULL, '2', '1 hora', '1 hora', '', '0'),
('9', '5', '1', '1', 'Serviços: Manutenção Preventiva', 'Média', 'Servico Realizado', '', '2026-06-22 20:18:53', '2026-06-23', '1', '1 hora', '1 hora', '', '0'),
('10', '8', '1', '1', 'Servicos: Instalação Elétrica', 'Média', 'Servico Realizado', '', '2026-06-22 22:51:29', '2026-06-23', '1', '1 hora', '1 hora', '00:00', '0'),
('11', '5', '1', NULL, 'Servicos: Instalação Elétrica', 'Média', 'Pendente', NULL, '2026-06-22 22:56:06', NULL, NULL, NULL, NULL, NULL, '0'),
('12', '5', '1', NULL, 'Servicos: Manutenção Preventiva', 'Média', 'Pendente', NULL, '2026-06-22 22:56:11', NULL, NULL, NULL, NULL, NULL, '0'),
('13', '5', '1', NULL, 'Servicos: Troca de Disjuntor', 'Média', 'Pendente', NULL, '2026-06-22 22:56:14', NULL, NULL, NULL, NULL, NULL, '0');

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
('1', 'Administrador', 'admin@opus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'admin', '2026-06-22 19:24:32'),
('2', 'João Silva', 'joao@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-1111', 'Rua das Acácias, 145, Jardim Paulista', 'Cianorte', 'PR', 'cliente', '2026-06-22 19:24:32'),
('3', 'Maria Souza', 'maria@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-2222', 'Rua Paraná, 320, Centro', 'Cianorte', 'PR', 'cliente', '2026-06-22 19:24:32'),
('4', 'Carlos Oliveira', 'carlos@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-3333', 'Av. Brasil, 890, Jardim Alvorada', 'Cianorte', 'PR', 'cliente', '2026-06-22 19:24:32'),
('5', 'Ana Paula Lima', 'ana@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-4444', 'Rua Minas Gerais, 210, Vila Nova', 'Cianorte', 'PR', 'cliente', '2026-06-22 19:24:32'),
('6', 'Pedro Almeida', 'pedro@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-5555', 'Rua São Paulo, 78, Jardim Santa Cruz', 'Maringá', 'PR', 'cliente', '2026-06-22 19:24:32'),
('7', 'Fernanda Costa', 'fernanda@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99821-6666', 'Av. Colombo, 440, Zona 07', 'Maringá', 'PR', 'cliente', '2026-06-22 19:24:32'),
('8', 'EletroLuz Resp', 'eletroluz@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1001', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-22 19:24:32'),
('9', 'Fretes PR Resp', 'fretes@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1002', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-22 19:24:32'),
('10', 'TechHelp Resp', 'techhelp@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1003', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-22 19:24:32'),
('11', 'HidroMaster Resp', 'hidro@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1004', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-22 19:24:32'),
('12', 'PintaBem Resp', 'pintabem@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1005', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-22 19:24:32'),
('13', 'LimpezaTotal Resp', 'limpeza@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1006', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-22 19:24:32'),
('14', 'JardimVivo Resp', 'jardim@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1007', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-22 19:24:32'),
('15', 'SegurMax Resp', 'segurmax@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1008', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-22 19:24:32'),
('16', 'SaúdeEmCasa Resp', 'saude@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1009', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-22 19:24:32'),
('17', 'ReformasPR Resp', 'reformas@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1010', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-22 19:24:32'),
('18', 'ArGelado Resp', 'argelado@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1011', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-22 19:24:32'),
('19', 'DedetizadoraCia Resp', 'dedetizadora@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1012', NULL, 'Cianorte', 'PR', 'empresa', '2026-06-22 19:24:32'),
('20', 'TechMar Resp', 'techmar@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1013', NULL, 'Maringá', 'PR', 'empresa', '2026-06-22 19:24:32'),
('21', 'MudançasFácil Resp', 'mudancas@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1014', NULL, 'Maringá', 'PR', 'empresa', '2026-06-22 19:24:32'),
('22', 'LimpeMax Resp', 'limpemax@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99811-1015', NULL, 'Maringá', 'PR', 'empresa', '2026-06-22 19:24:32');

SET FOREIGN_KEY_CHECKS=1;
