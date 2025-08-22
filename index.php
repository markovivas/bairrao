<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Tabela Brasileirão</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <?php
  include 'controller.php';
  $view = isset($_GET['view']) ? $_GET['view'] : 'tabela';
  ?>

  <h1>Tabela Brasileirão</h1>

  <?php if ($view === 'tabela'): ?>
    <form method="GET">
      <input type="text" name="busca" placeholder="Buscar time..." value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
      <input type="hidden" name="view" value="tabela">
      <button type="submit">Buscar</button>
    </form>

    <div class="container">
      <div class="tabela-section">
        <div class="tabela-wrapper">
          <table id="tabela">
            <thead>
              <tr>
                <th>#</th>
                <th>Time</th>
                <th><a href="?view=tabela&ordenar=pontos">P</a></th>
                <th><a href="?view=tabela&ordenar=jogos">J</a></th>
                <th><a href="?view=tabela&ordenar=vitorias">V</a></th>
                <th><a href="?view=tabela&ordenar=empates">E</a></th>
                <th><a href="?view=tabela&ordenar=derrotas">D</a></th>
                <th><a href="?view=tabela&ordenar=gols_pro">GP</a></th>
                <th><a href="?view=tabela&ordenar=gols_contra">GC</a></th>
                <th><a href="?view=tabela&ordenar=saldo_gols">SG</a></th>
                <th><a href="?view=tabela&ordenar=aproveitamento">%</a></th>
                <th>Últ. Jogos</th>
              </tr>
            </thead>
            <tbody>
              <?php displayTeams($conn); ?>
            </tbody>
          </table>
        </div>
        <div class="voltar-link-container">
          <a href="lancar.php" class="voltar-link">Lançar Resultado de Jogo</a>
          <a href="cadastro.php" class="voltar-link">Cadastrar Novo Jogo</a>
        </div>
      </div>

      <div class="jogos-section">
        <?php
        $rodada_atual = 1; // Valor padrão

        // Busca a última rodada com jogos cadastrados (com ou sem resultados)
        $sql = "SELECT MAX(rodada) as ultima_rodada FROM historico_jogos";
        $result = $conn->query($sql);
        $ultima_rodada = 1;
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (!empty($row['ultima_rodada'])) {
                $ultima_rodada = $row['ultima_rodada'];
                $rodada_atual = $ultima_rodada;
            }
        }

        // Se houver parâmetro rodada na URL, usa ele
        if (isset($_GET['rodada'])) {
            $rodada_atual = (int)$_GET['rodada'];
            if ($rodada_atual < 1) $rodada_atual = 1;
            if ($rodada_atual > $ultima_rodada) $rodada_atual = $ultima_rodada;
        }
        ?>
        <div class="jogos-nav">
          <button class="nav-arrow" onclick="mudarRodada(-1)" <?php echo $rodada_atual <= 1 ? 'disabled' : ''; ?>>&lt;</button>
          <div class="rodada-atual">Rodada <span id="rodada-numero"><?= $rodada_atual ?>ª</span></div>
          <button class="nav-arrow" onclick="mudarRodada(1)" <?php echo $rodada_atual >= $ultima_rodada ? 'disabled' : ''; ?>>&gt;</button>
        </div>
        <div id="jogos-rodada">
          <?php displayGames($conn, $rodada_atual); ?>
        </div>
      </div>
    </div>

    <script>
      function mudarRodada(delta) {
        const urlParams = new URLSearchParams(window.location.search);
        let rodadaAtual = parseInt(urlParams.get('rodada')) || <?php echo $rodada_atual; ?>;
        let novaRodada = rodadaAtual + delta;
        
        if (novaRodada < 1) novaRodada = 1;
        if (novaRodada > <?php echo $ultima_rodada; ?>) novaRodada = <?php echo $ultima_rodada; ?>;
        
        // Mantém outros parâmetros da URL (como busca)
        urlParams.set('rodada', novaRodada);
        urlParams.set('view', 'tabela');
        
        // Verifica se há parâmetro de busca para manter
        const busca = urlParams.get('busca');
        if (busca) {
            urlParams.set('busca', busca);
        }
        
        window.location.href = '?' + urlParams.toString();
      }
    </script>
  <?php endif; ?>
</body>
</html>