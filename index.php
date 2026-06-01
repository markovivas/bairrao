<?php
include 'controller.php';
$view = isset($_GET['view']) ? $_GET['view'] : 'tabela';
$campeonato_nome = ConfigModel::get($conn, 'campeonato_nome');
$campeonato_descricao = ConfigModel::get($conn, 'campeonato_descricao');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($campeonato_nome) ?> — <?= htmlspecialchars($campeonato_descricao) ?></title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚽</text></svg>">
  <link rel="stylesheet" href="estilo.css">
</head>
<body>

  <div class="page-header">
    <h1><?= htmlspecialchars($campeonato_nome) ?> <small><?= htmlspecialchars($campeonato_descricao) ?></small></h1>
  </div>

  <?php if ($view === 'tabela'): ?>
    <form method="GET" class="search-form">
      <input type="text" name="busca" placeholder="Buscar time..." value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
      <input type="hidden" name="view" value="tabela">
      <button type="submit">Buscar</button>
    </form>

    <div class="page-container">
      <div class="tabela-section">
        <div class="tabela-wrapper">
          <table id="tabela">
            <thead>
              <tr>
                <th>#</th>
                <th>Time</th>
                <?php
                $colunas = [
                  'pontos' => 'P',
                  'jogos' => 'J',
                  'vitorias' => 'V',
                  'empates' => 'E',
                  'derrotas' => 'D',
                  'gols_pro' => 'GP',
                  'gols_contra' => 'GC',
                  'saldo_gols' => 'SG',
                  'aproveitamento' => '%'
                ];
                $ordenar_atual = $_GET['ordenar'] ?? 'pontos';
                $ordem_atual = isset($_GET['ordem']) && $_GET['ordem'] === 'asc' ? 'asc' : 'desc';
                foreach ($colunas as $key => $label):
                  $nova_ordem = ($key === $ordenar_atual && $ordem_atual === 'desc') ? 'asc' : 'desc';
                  $active = $key === $ordenar_atual ? 'active-sort' : '';
                  $seta = $key === $ordenar_atual ? ($ordem_atual === 'desc' ? '&#9660;' : '&#9650;') : '';
                ?>
                  <th><a href="?view=tabela&amp;ordenar=<?= $key ?>&amp;ordem=<?= $nova_ordem ?>" class="<?= $active ?>"><?= $label ?> <span class="sort-arrow"><?= $seta ?></span></a></th>
                <?php endforeach; ?>
                <th>Últ. Jogos</th>
              </tr>
            </thead>
              <tbody>
              <?= exibirTabela($conn) ?>
              </tbody>
          </table>
        </div>
        <div class="voltar-link-container">
          <a href="admin/login.php" class="voltar-link">Painel Administrativo</a>
        </div>
      </div>

      <div class="jogos-section">
        <?php
        $ultima_rodada = JogoModel::ultimaRodada($conn);
        $rodada_atual = $ultima_rodada;

        if (isset($_GET['rodada'])) {
            $rodada_atual = max(1, min((int)$_GET['rodada'], $ultima_rodada));
        }
        ?>
        <div class="jogos-nav">
          <button class="nav-arrow" onclick="mudarRodada(-1)" <?php echo $rodada_atual <= 1 ? 'disabled' : ''; ?>>&lt;</button>
          <div class="rodada-atual">Rodada <span id="rodada-numero"><?= $rodada_atual ?>ª</span></div>
          <button class="nav-arrow" onclick="mudarRodada(1)" <?php echo $rodada_atual >= $ultima_rodada ? 'disabled' : ''; ?>>&gt;</button>
        </div>
        <div id="jogos-rodada">
          <?= exibirJogos($conn, $rodada_atual) ?>
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
        
        urlParams.set('rodada', novaRodada);
        urlParams.set('view', 'tabela');
        
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
