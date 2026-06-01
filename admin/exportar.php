<?php
require_once __DIR__ . '/../controller.php';
requerAdmin();

$times = TimeModel::listar($conn, 'pontos', 'DESC', '');
?>
<?php include '_header.php'; ?>

<div class="admin-section">
  <h2>Exportar Tabela</h2>
  <p style="margin-bottom:16px;">Baixe a classificação atual no formato CSV, compatível com Excel e Google Sheets.</p>
  <a href="../controller.php?action=export_csv" class="btn-admin btn-success" style="display:inline-block;padding:10px 24px;text-decoration:none;">📥 Baixar CSV</a>
</div>

<div class="admin-section">
  <h2>Pré-visualização dos Dados</h2>
  <table class="tabela">
    <thead>
      <tr><th>#</th><th>Time</th><th>P</th><th>J</th><th>V</th><th>E</th><th>D</th><th>GP</th><th>GC</th><th>SG</th><th>%</th></tr>
    </thead>
    <tbody>
      <?php $pos = 1; foreach ($times as $t): ?>
        <tr>
          <td><?= $pos++ ?></td>
          <td style="text-align:left;font-weight:700;"><?= htmlspecialchars($t['nome']) ?></td>
          <td><?= $t['pontos'] ?></td>
          <td><?= $t['jogos'] ?></td>
          <td><?= $t['vitorias'] ?></td>
          <td><?= $t['empates'] ?></td>
          <td><?= $t['derrotas'] ?></td>
          <td><?= $t['gols_pro'] ?></td>
          <td><?= $t['gols_contra'] ?></td>
          <td><?= $t['saldo_gols'] ?></td>
          <td><?= $t['aproveitamento'] ?>%</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include '_footer.php'; ?>
