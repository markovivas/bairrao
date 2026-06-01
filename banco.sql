CREATE TABLE times (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100),
  pontos INT,
  jogos INT,
  vitorias INT,
  empates INT,
  derrotas INT,
  gols_pro INT,
  gols_contra INT,
  saldo_gols INT,
  aproveitamento INT,
  ultimos_jogos VARCHAR(20)
);

INSERT INTO times (nome, pontos, jogos, vitorias, empates, derrotas, gols_pro, gols_contra, saldo_gols, aproveitamento, ultimos_jogos) VALUES
('Palmeiras', 0, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('Flamengo', 0, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('Fluminense', 0, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('Bragantino', 0, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('Ceará', 0, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('Corinthians', 0, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('Cruzeiro', 0, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('Vasco', 0, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('Juventude', 0, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('São Paulo', 0, 0, 0, 0, 0, 0, 0, 0, 0, '');

CREATE TABLE IF NOT EXISTS historico_jogos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  time_casa VARCHAR(100) NOT NULL,
  time_visitante VARCHAR(100) NOT NULL,
  gols_casa INT,
  gols_visitante INT,
  data_jogo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  rodada INT NOT NULL,
  CONSTRAINT check_different_teams CHECK (time_casa != time_visitante),
  INDEX idx_rodada (rodada),
  INDEX idx_data_jogo (data_jogo)
);

CREATE TABLE IF NOT EXISTS configuracoes (
  chave VARCHAR(50) PRIMARY KEY,
  valor VARCHAR(255) NOT NULL
);

INSERT INTO configuracoes (chave, valor) VALUES
('campeonato_nome', 'Brasileirão'),
('campeonato_descricao', 'Série A')
ON DUPLICATE KEY UPDATE valor = valor;