-- =============================================
-- CRIAR BANCO DE DADOS: grammy
-- Para uso com XAMPP / MySQL local
-- =============================================

-- 1. Cria ou usa o banco de dados grammy
CREATE DATABASE IF NOT EXISTS grammy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE grammy;

-- 2. Tabela: usuarios
-- Armazena os dados de login dos gerentes e jurados
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome_completo VARCHAR(255) NOT NULL COMMENT 'Nome completo do usuário (ex: Eduardo Azevedo)',
  nome_login VARCHAR(100) NOT NULL UNIQUE COMMENT 'Login único (ex: EduardoAzevedo)',
  senha VARCHAR(255) NOT NULL COMMENT 'Senha em texto puro ou hash',
  grupo ENUM('gerente', 'jurado') NOT NULL COMMENT 'Tipo de usuário',
  data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabela: apresentacoes
-- Armazena as apresentações cadastradas pelo gerente
CREATE TABLE IF NOT EXISTS apresentacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL COMMENT 'Título da apresentação',
  criador VARCHAR(255) NOT NULL COMMENT 'Nome da equipe ou criador',
  url TEXT NOT NULL COMMENT 'Link público do Google Drive',
  pontuacao_total DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Soma das médias recebidas dos jurados (para ranking)',
  data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabela: avaliacoes
-- Armazena as notas dadas pelos jurados
CREATE TABLE IF NOT EXISTS avaliacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  apresentacao_id INT NOT NULL COMMENT 'Referência à apresentação',
  jurado_nome VARCHAR(255) NOT NULL COMMENT 'Nome completo do jurado (igual a usuarios.nome_completo)',
  roteiro DECIMAL(3,1) NOT NULL,
  producao DECIMAL(3,1) NOT NULL,
  lipsync DECIMAL(3,1) NOT NULL,
  figurino DECIMAL(3,1) NOT NULL,
  sinergia DECIMAL(3,1) NOT NULL,
  criatividade DECIMAL(3,1) NOT NULL,
  performance DECIMAL(3,1) NOT NULL,
  revelacao DECIMAL(3,1) NOT NULL,
  arte_audiovisual DECIMAL(3,1) NOT NULL,
  media DECIMAL(4,2) NOT NULL COMMENT 'Média final da avaliação',
  data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (apresentacao_id) REFERENCES apresentacoes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Índice para melhorar desempenho nas consultas por jurado e apresentação
CREATE INDEX idx_jurado_nome ON avaliacoes(jurado_nome);
CREATE INDEX idx_apresentacao_id ON avaliacoes(apresentacao_id);

