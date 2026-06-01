-- OPUS | Backup automático gerado em 01/06/2026 17:34:10
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `avaliacao` VALUES
('1', '2', '1', '1', '5', 'Serviço excelente! Profissional pontual e muito cuidadoso. Recomendo!', '0', '2026-05-31 00:48:07'),
('2', '7', '2', '3', '1', 'uma merda', '0', '2026-05-31 16:19:25'),
('3', '7', '2', '4', '5', 'kkokokok', '0', '2026-05-31 16:20:10');

-- Tabela: backup_log
DROP TABLE IF EXISTS `backup_log`;
CREATE TABLE `backup_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `arquivo` varchar(255) DEFAULT NULL,
  `tamanho` int(11) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `backup_log` VALUES
('1', 'opus_backup_20260601_171843.sql', '9747', '2026-06-01 12:18:43'),
('2', 'opus_backup_20260601_172701.sql', '9882', '2026-06-01 12:27:01'),
('3', 'opus_backup_20260601_172937.sql', '9955', '2026-06-01 12:29:37'),
('4', 'opus_backup_20260601_173052.sql', '10028', '2026-06-01 12:30:52'),
('5', 'opus_backup_20260601_173104.sql', '10102', '2026-06-01 12:31:04');

-- Tabela: categoria
DROP TABLE IF EXISTS `categoria`;
CREATE TABLE `categoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `icone` varchar(10) DEFAULT '?',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categoria` VALUES
('1', 'Manutenção', '🔧'),
('2', 'Reforma', '🏠'),
('3', 'Transporte', '🚚'),
('4', 'Tecnologia', '💻'),
('5', 'Limpeza', '🧹'),
('6', 'Jardinagem', '🌿'),
('7', 'Segurança', '🔒'),
('8', 'Saúde', '🏥'),
('9', 'Movel', '🏠');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `empresa` VALUES
('1', '3', 'EletroLuz Cianorte', '12.345.678/0001-90', 'contato@eletroluz.com', '(44) 3372-1111', NULL, 'Cianorte', 'PR', 'Atendimento residencial e comercial, manutenção preventiva, troca de disjuntores e instalação de tomadas. Mais de 10 anos de experiência no mercado elétrico.', '1', '2026-05-31 00:48:07'),
('2', '4', 'Fretes Paraná', '98.765.432/0001-10', 'contato@fretespr.com', '(44) 3372-2222', NULL, 'Cianorte', 'PR', 'Equipe especializada em fretes urbanos, montagem simples e transporte seguro de móveis e eletrodomésticos.', '1', '2026-05-31 00:48:07'),
('3', '5', 'TechHelp', '55.444.333/0001-20', 'contato@techhelp.com', '(44) 3372-3333', NULL, 'Cianorte', 'PR', 'Formatação, manutenção, instalação de sistemas e suporte técnico para empresas e residências.', '1', '2026-05-31 00:48:07'),
('4', '6', 'Innovie Ambientes Planejados', '60.941.771/0001-05', 'vitorariano255@gmail.com', '(44) 99703-6938', 'Rua Santos Dumont, Jardim Angelo Liberati, 1115', 'Cianorte', 'PR', 'Móveis Planejados', '1', '2026-05-31 01:21:37');

-- Tabela: servico
DROP TABLE IF EXISTS `servico`;
CREATE TABLE `servico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco_medio` decimal(10,2) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `empresa_id` (`empresa_id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `servico_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `servico_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `servico` VALUES
('1', '1', '1', 'Instalação Elétrica', 'Instalação completa de tomadas, interruptores e quadro elétrico.', '350.00', '1', '2026-05-31 00:48:07'),
('2', '1', '1', 'Manutenção Preventiva', 'Revisão geral do sistema elétrico residencial.', '200.00', '1', '2026-05-31 00:48:07'),
('3', '1', '1', 'Troca de Disjuntor', 'Substituição de disjuntores defeituosos ou subdimensionados.', '120.00', '1', '2026-05-31 00:48:07'),
('4', '2', '3', 'Frete Urbano', 'Transporte de móveis e objetos dentro da cidade.', '150.00', '1', '2026-05-31 00:48:07'),
('5', '2', '3', 'Pequena Mudança', 'Mudança completa para residências de até 2 quartos.', '400.00', '1', '2026-05-31 00:48:07'),
('6', '3', '4', 'Formatação PC', 'Formatação e reinstalação do sistema operacional.', '90.00', '1', '2026-05-31 00:48:07'),
('7', '3', '4', 'Suporte Técnico', 'Diagnóstico e resolução de problemas em computadores e notebooks.', '80.00', '1', '2026-05-31 00:48:07'),
('8', '3', '4', 'Instalação de Rede', 'Configuração de redes Wi-Fi e cabeadas para empresas.', '250.00', '1', '2026-05-31 00:48:07'),
('9', '4', '9', 'Movel', 'Movel', '99999999.99', '1', '2026-05-31 01:23:04');

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
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `solicitacao_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  CONSTRAINT `solicitacao_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `solicitacao` VALUES
('1', '2', '1', 'Preciso instalar 4 tomadas novas no quarto e sala.', 'Média', 'Concluído', 'Orçamento aceito. Serviço realizado em 2h. Tomadas instaladas conforme solicitado.', '2026-05-31 00:48:07', '2026-05-20'),
('2', '2', '3', 'Meu notebook não liga mais, preciso de diagnóstico.', 'Alta', 'Em andamento', 'Diagnóstico realizado: fonte danificada. Aguardando peça.', '2026-05-31 00:48:07', NULL),
('3', '7', '2', 'jiojiojjjoioij', 'Média', 'Concluído', 'ok', '2026-05-31 16:17:12', '2026-05-31'),
('4', '7', '2', 'jokjoijijjojijio', 'Alta', 'Concluído', 'okk', '2026-05-31 16:19:51', '2026-05-31');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuario` VALUES
('1', 'Administrador', 'admin@opus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, 'admin', '2026-05-31 00:48:07'),
('2', 'João Silva', 'joao@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99999-1111', NULL, 'Cianorte', 'PR', 'cliente', '2026-05-31 00:48:07'),
('3', 'EletroLuz Admin', 'eletroluz@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99999-2222', NULL, 'Cianorte', 'PR', 'empresa', '2026-05-31 00:48:07'),
('4', 'Fretes Paraná Admin', 'fretes@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99999-3333', NULL, 'Cianorte', 'PR', 'empresa', '2026-05-31 00:48:07'),
('5', 'TechHelp Admin', 'techhelp@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(44) 99999-4444', NULL, 'Cianorte', 'PR', 'empresa', '2026-05-31 00:48:07'),
('6', 'Vitor Apolinario Ariano', 'vitorariano255@gmail.com', '$2y$10$TcokEXSdxnyu5hkbTZHVLOq.qByz7KpHivPEUMIxXSwNF9Mn5lBl.', '(44) 99703-6938', NULL, NULL, NULL, 'empresa', '2026-05-31 01:21:37'),
('7', 'Gabriel Balbino', 'balbinogabrieloliveira@gmail.com', '$2y$10$7JRjMmL.4DfFp/gjgcPkqOHp5ttsoqxNlYmOHdvIThhYznHnQO0s.', '(44) 99874-4826', NULL, NULL, NULL, 'cliente', '2026-05-31 16:16:01');

SET FOREIGN_KEY_CHECKS=1;
