# Sistema de Gerenciamento do Campeonato Brasileiro

Este sistema permite gerenciar a tabela do Campeonato Brasileiro, cadastrar jogos e lançar resultados de partidas.

## 📋 Funcionalidades

- **Visualização da Tabela**: Exibe a classificação dos times com estatísticas completas (pontos, jogos, vitórias, empates, derrotas, gols, aproveitamento)
- **Busca e Ordenação**: Permite buscar times e ordenar a tabela por diferentes critérios
- **Cadastro de Jogos**: Interface para cadastrar novos jogos com data, horário e rodada
- **Lançamento de Resultados**: Sistema para registrar os resultados das partidas
- **Histórico de Jogos**: Visualização dos jogos por rodada com navegação entre rodadas
- **Autenticação**: Proteção por senha para operações administrativas

## 🗃️ Estrutura do Banco de Dados

### Tabela `times`
Armazena as estatísticas e informações dos times:
- `id`: Identificador único (auto incremento)
- `nome`: Nome do time
- `pontos`, `jogos`, `vitorias`, `empates`, `derrotas`: Estatísticas do campeonato
- `gols_pro`, `gols_contra`, `saldo_gols`: Estatísticas de gols
- `aproveitamento`: Percentual de aproveitamento
- `ultimos_jogos`: Histórico dos últimos resultados (V, E, D)

### Tabela `historico_jogos`
Armazena o histórico de todos os jogos:
- `id`: Identificador único (auto incremento)
- `time_casa`, `time_visitante`: Times participantes
- `gols_casa`, `gols_visitante`: Resultado do jogo (NULL se não realizado)
- `data_jogo`: Data e hora do jogo
- `rodada`: Número da rodada
- Restrições: Impede jogos entre o mesmo time

## 🚀 Como Usar

### Pré-requisitos
- Servidor web com PHP
- Banco de dados MySQL
- Extensão MySQLi habilitada no PHP

### Instalação
1. Importe o arquivo `banco.sql` no seu MySQL para criar as tabelas
2. Configure a conexão com o banco no arquivo `controller.php`:
   ```php
   $conn = new mysqli("localhost", "username", "password", "database_name");
   ```
3. Defina a senha de administração no mesmo arquivo:
   ```php
   define('ADMIN_PASSWORD', 'sua_senha_aqui');
   ```
4. Coloque os escudos dos times na pasta `escudos/` no formato PNG (nome do arquivo deve corresponder ao nome do time sem caracteres especiais)

### Navegação
- **Página Principal**: `index.php` - Exibe a tabela e jogos da rodada
- **Cadastrar Jogo**: `cadastro.php` - Interface para adicionar novos jogos
- **Lançar Resultado**: `lancar.php` - Interface para registrar resultados

## 🔐 Segurança

- Operações administrativas exigem senha (definida em `ADMIN_PASSWORD`)
- Validação de dados em ambos front-end e back-end
- Proteção contra SQL injection usando `real_escape_string`
- Verificação para evitar times iguais em uma partida

## 🎨 Personalização

### Escudos dos Times
Os escudos devem ser colocados na pasta `escudos/` com os nomes correspondentes aos times, sem caracteres especiais e em minúsculas. Exemplo:
- palmeiras.png
- flamengo.png
- fluminense.png

### Estilização
O arquivo `estilo.css` contém todos os estilos do sistema, incluindo:
- Design responsivo para mobile e desktop
- Cores temáticas do campeonato brasileiro
- Animações e transições suaves

## ⚙️ Funcionalidades Técnicas

### No Back-end (`controller.php`)
- Exibição e ordenação da tabela
- Gerenciamento de jogos e resultados
- Atualização automática de estatísticas
- Validação de dados e autenticação

### No Front-end
- Interface responsiva e intuitiva
- Validação em tempo real
- Navegação por rodadas
- Busca e filtros

## 📞 Suporte

Para problemas ou dúvidas, verifique:
1. Conexão com o banco de dados
2. Permissões de arquivo na pasta `escudos/`
3. Configuração do PHP (MySQLi habilitado)

Este sistema oferece uma solução completa para gerenciamento e acompanhamento de campeonatos de futebol com interface amigável e funcionalidades robustas.