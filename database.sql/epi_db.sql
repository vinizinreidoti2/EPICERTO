-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25/11/2026 às 15:40
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `epi_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamados_senha`
--

CREATE TABLE `chamados_senha` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `identificador` varchar(100) NOT NULL,
  `usuario_nome` varchar(100) NOT NULL,
  `setor` varchar(50) NOT NULL,
  `observacao` varchar(255) DEFAULT NULL,
  `status` enum('pendente','resolvido') DEFAULT 'pendente',
  `data_solicitacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `epis`
--

CREATE TABLE `epis` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `numero_ca` varchar(30) NOT NULL DEFAULT 'Pendente',
  `validade_ca` date DEFAULT NULL,
  `quantidade` int(11) NOT NULL,
  `estoque_minimo` int(11) NOT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `epis`
--

INSERT INTO `epis` (`id`, `nome`, `numero_ca`, `validade_ca`, `quantidade`, `estoque_minimo`, `status`) VALUES
(1, 'Óculos de Segurança', 'CA 11268', '2028-06-15', 50, 10, 'ativo'),
(2, 'Luva Nitrílica', 'CA 35124', '2026-11-27', 80, 15, 'ativo'),
(3, 'Capacete de Proteção', 'CA 31469', '2027-04-14', 30, 5, 'inativo'),
(4, 'Protetor Auricular Tipo Plug', 'CA 12189', '2027-08-20', 100, 20, 'inativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `retiradas`
--

CREATE TABLE `retiradas` (
  `id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `funcionario_nome` varchar(100) NOT NULL,
  `epi_nome` varchar(100) NOT NULL,
  `metodo` varchar(50) DEFAULT 'Caixa Inteligente',
  `justificativa` text DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `retiradas`
--

INSERT INTO `retiradas` (`id`, `funcionario_id`, `funcionario_nome`, `epi_nome`, `metodo`, `justificativa`, `data_hora`) VALUES
(1, 2, 'João da Silva', 'Luva Nitrílica', 'Caixa Inteligente', 'Solicitação Regular', '2026-11-25 08:20:30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacoes`
--

CREATE TABLE `solicitacoes` (
  `id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `funcionario_nome` varchar(100) NOT NULL,
  `epi_nome` varchar(100) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `justificativa` text DEFAULT NULL,
  `status` enum('pendente','aprovado','recusado') DEFAULT 'pendente',
  `data_solicitacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `solicitacoes`
--

INSERT INTO `solicitacoes` (`id`, `funcionario_id`, `funcionario_nome`, `epi_nome`, `motivo`, `justificativa`, `status`, `data_solicitacao`) VALUES
(1, 2, 'João da Silva', 'Luva Nitrílica', 'Equipamento Danificado / Quebrado', NULL, 'aprovado', '2026-11-25 08:20:09');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `matricula` varchar(20) NOT NULL,
  `codigo_cracha` varchar(50) DEFAULT NULL,
  `setor` varchar(50) NOT NULL,
  `perfil` enum('gestor','funcionario') NOT NULL,
  `primeiro_acesso` int(11) DEFAULT 1,
  `termo_aceito` tinyint(1) NOT NULL DEFAULT 0,
  `data_aceite_termo` datetime DEFAULT NULL,
  `ip_aceite` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `matricula`, `codigo_cracha`, `setor`, `perfil`, `primeiro_acesso`, `termo_aceito`, `data_aceite_termo`, `ip_aceite`) VALUES
(1, 'Pablo Milhuma', 'gestor@empresa.com', '$2y$10$nej2v922mBDDVyuO7hBZWOYyXYsKnPs6cmt1ca7YVnu4dtbC/YYKW', 'MAT000', 'CR-000000', 'TI / Gestão', 'gestor', 0, 1, '2026-11-25 09:03:31', NULL),
(2, 'João da Silva', 'joao.silva@empresa.com', '$2y$10$OQz2E2WDdwIMDQlI6Yvu5.1uDyDYMBEcOICYcXcWAOi2pARuvjO8K', 'MAT001', 'CR-998877', 'Produção', 'funcionario', 0, 0, NULL, NULL),
(3, 'STATI', 'stati@empresa.com', '$2y$10$pN2UZS5jAwO1uY8M.GxQV.zJ77O3uyZ0qxt3a2w1YZqGdboHkNkla', 'MAT003', '', 'TI / Gestão', 'gestor', 1, 0, NULL, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `chamados_senha`
--
ALTER TABLE `chamados_senha`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `epis`
--
ALTER TABLE `epis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `retiradas`
--
ALTER TABLE `retiradas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `solicitacoes`
--
ALTER TABLE `solicitacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `matricula` (`matricula`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `chamados_senha`
--
ALTER TABLE `chamados_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `epis`
--
ALTER TABLE `epis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `retiradas`
--
ALTER TABLE `retiradas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `solicitacoes`
--
ALTER TABLE `solicitacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
