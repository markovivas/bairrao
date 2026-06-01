<?php
require_once __DIR__ . '/../controller.php';
requerAdmin();

$times = TimeModel::listar($conn, 'pontos', 'DESC', '');

$mensagens = [
  'success' => ['3' => 'Time adicionado com sucesso!', '4' => 'Time removido com sucesso!'],
  'error' => ['5' => 'Nome do time inválido.', '7' => 'Erro ao processar.', '8' => 'Nome do time não pode estar vazio.', '9' => 'Não é possível remover um time que possui jogos.']
];
?>
<?php include '_header.php'; ?>

<?php foreach (['success', 'error'] as $tipo):
  $v = $_GET[$tipo] ?? '';
  if ($v && isset($mensagens[$tipo][$v])): ?>
    <div style="padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;<?= $tipo === 'success' ? 'background:#d4edda;color:#155724' : 'background:#f8d7da;color:#721c24' ?>">
      <?= $mensagens[$tipo][$v] ?>
    </div>
<?php endif; endforeach; ?>

<div class="admin-section">
  <h2>Adicionar Time</h2>
  <form method="POST" action="../controller.php?action=add_team" class="inline-form">
    <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
    <input type="text" name="nome" placeholder="Nome do time" required style="padding:8px;border:2px solid #e0e0e0;border-radius:6px;flex:1;min-width:200px;">
    <button type="submit" class="btn-admin btn-success">Adicionar</button>
  </form>
</div>

<div class="admin-section">
  <h2>Times (<?= count($times) ?>)</h2>
  <table class="tabela">
    <thead>
      <tr><th>#</th><th>Time</th><th>P</th><th>J</th><th>V</th><th>E</th><th>D</th><th>GP</th><th>GC</th><th>SG</th><th>%</th><th>Ação</th></tr>
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
          <td>
            <form method="POST" action="../controller.php?action=delete_team" onsubmit="return confirm('Remover <?= htmlspecialchars($t['nome'], ENT_QUOTES) ?>?')">
              <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
              <input type="hidden" name="nome" value="<?= htmlspecialchars($t['nome']) ?>">
              <button type="submit" class="btn-admin btn-danger" style="padding:4px 10px;font-size:0.8em;">Remover</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include '_footer.php'; ?>
