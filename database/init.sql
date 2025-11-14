-- Inicialização do Banco de Dados - Sistema de Cadastro
CREATE DATABASE IF NOT EXISTS sistema_cadastro;
USE sistema_cadastro;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    cidade VARCHAR(50),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dados de exemplo para demonstração
INSERT IGNORE INTO usuarios (nome, email, telefone, cidade) VALUES
('João Silva', 'joao.silva@email.com', '(11) 9999-8888', 'São Paulo'),
('Maria Santos', 'maria.santos@email.com', '(21) 7777-6666', 'Rio de Janeiro'),
('Pedro Oliveira', 'pedro.oliveira@email.com', '(31) 5555-4444', 'Belo Horizonte');

-- Usuário específico para a aplicação
CREATE USER IF NOT EXISTS 'usuario_app'@'%' IDENTIFIED BY 'senha123';
GRANT ALL PRIVILEGES ON sistema_cadastro.* TO 'usuario_app'@'%';
FLUSH PRIVILEGES;