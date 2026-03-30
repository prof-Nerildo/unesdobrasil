-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20251126.a700ba5407
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 30, 2026 at 12:21 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `unesdobrasil`
--

-- --------------------------------------------------------

--
-- Table structure for table `acl`
--

CREATE TABLE `acl` (
  `idAcl` int NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `acl`
--

INSERT INTO `acl` (`idAcl`, `tipo`, `descricao`) VALUES
(1, 'Master', 'Acesso total ao sistema'),
(2, 'Unes', 'Acesso administrativo da UNES'),
(3, 'Instituição de Ensino', 'Acesso para escolas e universidades'),
(4, 'Aluno', 'Acesso restrito do estudante');

-- --------------------------------------------------------

--
-- Table structure for table `contato`
--

CREATE TABLE `contato` (
  `idContato` int NOT NULL,
  `idReferencia` int NOT NULL,
  `tipo_entidade` enum('usuario','instituicao') NOT NULL,
  `tipo_contato` enum('celular','fixo','fax','email_secretaria') NOT NULL,
  `valor` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `endereco`
--

CREATE TABLE `endereco` (
  `idEndereco` int NOT NULL,
  `idReferencia` int NOT NULL,
  `tipo_entidade` enum('usuario','instituicao') NOT NULL,
  `tipo_endereco` enum('comercial','residencial') DEFAULT 'comercial',
  `cep` varchar(10) DEFAULT NULL,
  `logradouro` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `uf` char(2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instituicao`
--

CREATE TABLE `instituicao` (
  `idInstituicao` int NOT NULL,
  `razao_social` varchar(255) NOT NULL,
  `nome_fantasia` varchar(255) DEFAULT NULL,
  `cnpj` varchar(20) NOT NULL,
  `insc_estadual` varchar(50) DEFAULT NULL,
  `insc_municipal` varchar(50) DEFAULT NULL,
  `valor_documento_nacional` decimal(10,2) DEFAULT '0.00',
  `valor_frete` decimal(10,2) DEFAULT '0.00',
  `pode_editar_instituicao` enum('sim','nao') DEFAULT 'nao',
  `pode_editar_curso` enum('sim','nao') DEFAULT 'nao',
  `idStatus` int DEFAULT '3',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instituicao_catraca`
--

CREATE TABLE `instituicao_catraca` (
  `idCatraca` int NOT NULL,
  `idInstituicao` int NOT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `quantidade` int DEFAULT '1',
  `usa_catraca` enum('sim','nao') DEFAULT 'sim'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `perfil`
--

CREATE TABLE `perfil` (
  `idPerfil` int NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `perfil`
--

INSERT INTO `perfil` (`idPerfil`, `tipo`, `descricao`) VALUES
(1, 'AdmMaster', 'Administrador Master do Sistema'),
(2, 'AdministradorUnes', 'Gestor da UNES'),
(3, 'ColaboradorUnes', 'Funcionário da UNES'),
(4, 'AdministradorInstituicao', 'Gestor da Escola/Faculdade'),
(5, 'ColaboradorInstituicao', 'Funcionário da Escola/Faculdade'),
(6, 'Aluno', 'Estudante matriculado');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `idStatus` int NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`idStatus`, `tipo`, `descricao`) VALUES
(1, 'Inativo', 'Usuário bloqueado ou desativado'),
(2, 'Ativo', 'Usuário/IstEnsino - com acesso liberado'),
(3, 'Pendente', 'Instituição de Ensino aguardando aprovação'),
(4, 'Suspenso', 'Instituição de Ensino aguardando regularização');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `idUsuario` int NOT NULL,
  `idAcl` int NOT NULL,
  `idStatus` int NOT NULL DEFAULT '2',
  `idPerfil` int NOT NULL,
  `idInstituicao` int DEFAULT NULL,
  `primeiro_nome` varchar(100) NOT NULL,
  `sobrenome` varchar(100) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `username` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expira_em` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`idUsuario`, `idAcl`, `idStatus`, `idPerfil`, `idInstituicao`, `primeiro_nome`, `sobrenome`, `cargo`, `email`, `username`, `senha`, `reset_token`, `reset_token_expira_em`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, NULL, 'web', 'DNA', 'Dev', 'nviana@webdna.com.br', 'webDNA', '$2y$10$uW38A3wpkNqQ2xh7WARMcOyHmYdQOuqsURBf6M9IdWav36DQOlDui', NULL, NULL, NULL, '2026-03-30 00:19:47', '2026-03-30 00:19:47'),
(2, 2, 2, 2, NULL, 'Nerildo', 'Viana', 'Dev WebDNA', 'atendimento@webdna.com.br', 'adminWebDNA', '$2y$10$O4Lp0B4IROSD5zBjHlJir.EhQruFeIfYPeKZI9e3TrCAbjFx2MZp6', NULL, NULL, NULL, '2026-03-30 00:19:47', '2026-03-30 00:19:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acl`
--
ALTER TABLE `acl`
  ADD PRIMARY KEY (`idAcl`);

--
-- Indexes for table `contato`
--
ALTER TABLE `contato`
  ADD PRIMARY KEY (`idContato`);

--
-- Indexes for table `endereco`
--
ALTER TABLE `endereco`
  ADD PRIMARY KEY (`idEndereco`);

--
-- Indexes for table `instituicao`
--
ALTER TABLE `instituicao`
  ADD PRIMARY KEY (`idInstituicao`),
  ADD UNIQUE KEY `cnpj` (`cnpj`),
  ADD KEY `idStatus` (`idStatus`);

--
-- Indexes for table `instituicao_catraca`
--
ALTER TABLE `instituicao_catraca`
  ADD PRIMARY KEY (`idCatraca`),
  ADD KEY `idInstituicao` (`idInstituicao`);

--
-- Indexes for table `perfil`
--
ALTER TABLE `perfil`
  ADD PRIMARY KEY (`idPerfil`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`idStatus`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idAcl` (`idAcl`),
  ADD KEY `idStatus` (`idStatus`),
  ADD KEY `idPerfil` (`idPerfil`),
  ADD KEY `idInstituicao` (`idInstituicao`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acl`
--
ALTER TABLE `acl`
  MODIFY `idAcl` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contato`
--
ALTER TABLE `contato`
  MODIFY `idContato` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `endereco`
--
ALTER TABLE `endereco`
  MODIFY `idEndereco` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instituicao`
--
ALTER TABLE `instituicao`
  MODIFY `idInstituicao` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instituicao_catraca`
--
ALTER TABLE `instituicao_catraca`
  MODIFY `idCatraca` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `perfil`
--
ALTER TABLE `perfil`
  MODIFY `idPerfil` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `idStatus` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idUsuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `instituicao`
--
ALTER TABLE `instituicao`
  ADD CONSTRAINT `instituicao_ibfk_1` FOREIGN KEY (`idStatus`) REFERENCES `status` (`idStatus`);

--
-- Constraints for table `instituicao_catraca`
--
ALTER TABLE `instituicao_catraca`
  ADD CONSTRAINT `instituicao_catraca_ibfk_1` FOREIGN KEY (`idInstituicao`) REFERENCES `instituicao` (`idInstituicao`) ON DELETE CASCADE;

--
-- Constraints for table `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`idAcl`) REFERENCES `acl` (`idAcl`),
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`idStatus`) REFERENCES `status` (`idStatus`),
  ADD CONSTRAINT `usuario_ibfk_3` FOREIGN KEY (`idPerfil`) REFERENCES `perfil` (`idPerfil`),
  ADD CONSTRAINT `usuario_ibfk_4` FOREIGN KEY (`idInstituicao`) REFERENCES `instituicao` (`idInstituicao`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
