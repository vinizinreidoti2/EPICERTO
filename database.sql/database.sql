CREATE DATABASE IF NOT EXISTS epi_db;
USE epi_db;

-- 1. Tabela de Usuários (Com suporte a senhas criptografadas e primeiro acesso)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    matricula VARCHAR(20) UNIQUE NOT NULL,
    codigo_cracha VARCHAR(50),
    setor VARCHAR(50) NOT NULL,
    perfil ENUM('gestor', 'funcionario') NOT NULL,
    primeiro_acesso INT DEFAULT 1
);

-- 2. Tabela de Estoque de EPIs
CREATE TABLE IF NOT EXISTS epis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) UNIQUE NOT NULL,
    quantidade INT NOT NULL,
    estoque_minimo INT NOT NULL
);

-- 3. Tabela de Histórico de Retiradas
CREATE TABLE IF NOT EXISTS retiradas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_nome VARCHAR(100) NOT NULL,
    epi_nome VARCHAR(100) NOT NULL,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    metodo VARCHAR(50) NOT NULL
);

-- 4. Inserção de dados de teste (A hash abaixo corresponde à senha '123456')
-- Pablo (Gestor): sem primeiro acesso (0)
-- João (Funcionário): com primeiro acesso obrigatório (1)
INSERT INTO usuarios (nome, email, senha, matricula, codigo_cracha, setor, perfil, primeiro_acesso) VALUES
('Pablo Milhuma', 'pablo.milhuma@empresa.com', '$2y$10$e8O0A7W62x54j.4jR.NlveHn6CqL8JkZJ3i.C8x9wG7zP2Z8YjKpO', 'MAT000', 'CR-000000', 'TI / Gestão', 'gestor', 0),
('João da Silva', 'joao.silva@empresa.com', '$2y$10$e8O0A7W62x54j.4jR.NlveHn6CqL8JkZJ3i.C8x9wG7zP2Z8YjKpO', 'MAT001', 'CR-998877', 'Produção', 'funcionario', 1)
ON DUPLICATE KEY UPDATE id=id;

-- EPIs de teste iniciais
INSERT INTO epis (nome, quantidade, estoque_minimo) VALUES
('Óculos de Segurança', 50, 10),
('Luva Nitrílica', 80, 15),
('Capacete de Proteção', 30, 5)
ON DUPLICATE KEY UPDATE id=id;