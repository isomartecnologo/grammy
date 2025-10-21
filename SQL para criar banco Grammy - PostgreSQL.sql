-- ============================================
-- BANCO DE DADOS: grammy (PostgreSQL)
-- Sistema: Grammy 2025 - Avaliação de Projetos
-- ============================================

-- Criação do esquema (opcional, usa o padrão 'public')
-- Se quiser usar um schema específico, descomente:
-- CREATE SCHEMA IF NOT EXISTS grammy;

-- --------------------------------------------
-- Tabela: usuarios
-- Armazena os usuários (gerentes e jurados)
-- --------------------------------------------
CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  nome_completo VARCHAR(255) NOT NULL,
  nome_login VARCHAR(100) NOT NULL UNIQUE,
  senha TEXT NOT NULL, -- hash da senha (ex: bcrypt)
  grupo VARCHAR(20) NOT NULL CHECK (grupo IN ('gerente', 'jurado')),
  data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índice para login (para busca rápida)
CREATE INDEX idx_usuarios_login ON usuarios(nome_login);

-- --------------------------------------------
-- Tabela: apresentacoes
-- Apresentações cadastradas pelo gerente
-- Tabela: apresentacoes (PostgreSQL)
CREATE TABLE apresentacoes (
  id SERIAL PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL,
  criador VARCHAR(255) NOT NULL,
  url TEXT NOT NULL,
  pontuacao_total DECIMAL(10,2) DEFAULT 0.00, -- Soma das médias recebidas dos jurados (para ranking)
  data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Comentário no campo (opcional, para documentação)
COMMENT ON COLUMN apresentacoes.pontuacao_total IS 'Soma das médias recebidas dos jurados (para ranking)';
-- Índice para título
CREATE INDEX idx_apresentacoes_titulo ON apresentacoes(titulo);

-- --------------------------------------------
-- Tabela: avaliacoes
-- Notas dadas pelos jurados
-- --------------------------------------------
CREATE TABLE avaliacoes (
  id SERIAL PRIMARY KEY,
  apresentacao_id INTEGER NOT NULL,
  jurado_nome VARCHAR(100) NOT NULL,
  nota_roteiro DECIMAL(3,1), -- 0.0 a 10.0
  nota_producao DECIMAL(3,1),
  nota_lipsinch DECIMAL(3,1),
  nota_figurino DECIMAL(3,1),
  nota_sinergia DECIMAL(3,1),
  nota_criatividade DECIMAL(3,1),
  nota_coreografia DECIMAL(3,1),
  nota_artistas DECIMAL(3,1),
  nota_caracterizacao DECIMAL(3,1),
  media DECIMAL(4,2), -- ex: 8.75
  data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  -- Chave estrangeira
  CONSTRAINT fk_apresentacao FOREIGN KEY (apresentacao_id)
    REFERENCES apresentacoes(id) ON DELETE CASCADE
);

-- Índice para avaliações por apresentação
CREATE INDEX idx_avaliacoes_apresentacao ON avaliacoes(apresentacao_id);
CREATE INDEX idx_avaliacoes_jurado ON avaliacoes(jurado_nome);

-- --------------------------------------------
-- (Opcional) Inserir dados de teste
-- --------------------------------------------
/*
INSERT INTO usuarios (nome_completo, nome_login, senha, grupo) VALUES
('Administrador do Sistema', 'admin', '$2y$10$.examplehashstring...', 'gerente'),
('Jurado Oficial', 'jurado1', '$2y$10$.examplehashstring...', 'jurado');

INSERT INTO apresentacoes (titulo, criador, url) VALUES
('Dança Criativa', 'Equipe A', 'https://drive.google.com/drive/folders/...'),
('Curta Amazônia', 'Equipe B', 'https://drive.google.com/drive/folders/...');
*/