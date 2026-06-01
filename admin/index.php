<?php
require_once __DIR__ . '/../controller.php';
requerAdmin();

$total_times = count(TimeModel::listar($conn, 'pontos', 'DESC', ''));
$total_jogos = JogoModel::totalJogos($conn);
$jogos_com_resultado = JogoModel::totalComResultado($conn);
$ultima_rodada = JogoModel::ultimaRodada($conn);
$times_topo = TimeModel::listar($conn, 'pontos', 'DESC', '', 4);
?>
<?php include '_header.php'; ?>

  <div class="admin-grid">
    <div class="admin-card">
      <div class="numero"><?= $total_times ?></div>
      <div class="rotulo">Times</div>
    </div>
    <div class="admin-card">
      <div class="numero"><?= $total_jogos ?></div>
      <div class="rotulo">Jogos Cadastrados</div>
    </div>
    <div class="admin-card">
      <div class="numero"><?= $jogos_com_resultado ?></div>
      <div class="rotulo">Resultados Lançados</div>
    </div>
    <div class="admin-card">
      <div class="numero"><?= $ultima_rodada ?></div>
      <div class="rotulo">Última Rodada</div>
    </div>
  </div>

  <div class="admin-section">
    <h2>Classificação Atual</h2>
    <table class="tabela"><tbody><?= exibirTabela($conn) ?></tbody></table>
  </div>

<?php include '_footer.php'; ?>
