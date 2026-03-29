-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20251126.a700ba5407
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 29, 2026 at 08:38 PM
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
  `descricao` varchar(100) DEFAULT NULL
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
-- Table structure for table `perfil`
--

CREATE TABLE `perfil` (
  `idPerfil` int NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `descricao` varchar(100) DEFAULT NULL
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
  `descricao` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`idStatus`, `tipo`, `descricao`) VALUES
(1, 'Inativo', 'Usuário bloqueado ou desativado'),
(2, 'Ativo', 'Usuário com acesso liberado');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `idUsuario` int NOT NULL,
  `idAcl` int NOT NULL,
  `idStatus` int NOT NULL,
  `idPerfil` int NOT NULL,
  `primeiro_nome` varchar(60) NOT NULL,
  `sobrenome` varchar(100) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `username` varchar(60) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expira_em` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acl`
--
ALTER TABLE `acl`
  ADD PRIMARY KEY (`idAcl`);

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
  ADD KEY `fk_usuario_acl` (`idAcl`),
  ADD KEY `fk_usuario_status` (`idStatus`),
  ADD KEY `fk_usuario_perfil` (`idPerfil`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acl`
--
ALTER TABLE `acl`
  MODIFY `idAcl` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `perfil`
--
ALTER TABLE `perfil`
  MODIFY `idPerfil` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `idStatus` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idUsuario` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_acl` FOREIGN KEY (`idAcl`) REFERENCES `acl` (`idAcl`),
  ADD CONSTRAINT `fk_usuario_perfil` FOREIGN KEY (`idPerfil`) REFERENCES `perfil` (`idPerfil`),
  ADD CONSTRAINT `fk_usuario_status` FOREIGN KEY (`idStatus`) REFERENCES `status` (`idStatus`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
