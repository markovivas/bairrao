<?php
require_once __DIR__ . '/../controller.php';
requerAdmin();

$nomes = TimeModel::listarNomes($conn);
$preview = $_SESSION['preview_rounds'] ?? null;
$formato = $_SESSION['preview_formato'] ?? 'ida';
?>
<?php include '_header.php'; ?>

<?php
$v = $_GET['success'] ?? '';
if ($v === '1'): ?>
  <div style="padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;background:#d4edda;color:#155724;">
    Tabela gerada com sucesso! Todos os jogos foram criados.
  </div>
<?php endif; ?>

<?php
$e = $_GET['error'] ?? '';
$erros = [
  '2' => 'É necessário pelo menos 2 times cadastrados.',
  '3' => 'Nenhuma tabela para salvar. Gere uma prévia primeiro.',
  '4' => 'Formato inválido.',
  '7' => 'Erro ao salvar a tabela no banco de dados.',
];
if ($e && isset($erros[$e])): ?>
  <div style="padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;background:#f8d7da;color:#721c24;">
    <?= $erros[$e] ?>
  </div>
<?php endif; ?>

<?php if ($preview): ?>
  <?php $total_jogos = 0; foreach ($preview as $r) $total_jogos += count($r); ?>
  <div class="admin-section">
    <h2>Prévia da Tabela Gerada</h2>
    <p style="margin-bottom:16px;color:#636e72;">
      <?= count($nomes) ?> times &middot; <?= count($preview) ?> rodadas &middot; <?= $total_jogos ?> jogos
      &middot; Formato: <strong><?= $formato === 'ida' ? 'Somente Ida' : 'Ida e Volta' ?></strong>
    </p>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(380px,1fr));gap:12px;">
      <?php foreach ($preview as $r => $matches): ?>
        <div style="background:#f8f9fa;border-radius:8px;padding:12px;">
          <h4 style="font-size:0.9em;color:#006341;margin-bottom:8px;border-bottom:1px solid #dee2e6;padding-bottom:4px;">
            Rodada <?= $r + 1 ?>
          </h4>
          <?php foreach ($matches as $m): ?>
            <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:0.85em;">
              <span style="text-align:right;flex:1;font-weight:600;"><?= htmlspecialchars($m['home']) ?></span>
              <span style="padding:0 12px;color:#636e72;">×</span>
              <span style="flex:1;font-weight:600;"><?= htmlspecialchars($m['away']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
      <form method="POST" action="../controller.php?action=gerar_salvar" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
        <button type="submit" class="btn-admin btn-success"
          onclick="return confirm('Isto vai apagar TODOS os jogos e resultados existentes e gerar uma nova tabela. Confirmar?')">
          &#10003; Aprovar e Salvar
        </button>
      </form>
      <a href="gerar.php" class="btn-admin btn-outline">&#8635; Regenerar</a>
    </div>
  </div>

<?php else: ?>
  <div class="admin-section">
    <h2>Gerar Tabela do Campeonato</h2>

    <?php if (count($nomes) < 2): ?>
      <p style="color:#e74c3c;">É necessário cadastrar pelo menos 2 times para gerar a tabela.</p>
      <a href="times.php" class="btn-admin btn-success">Ir para Times</a>
    <?php else: ?>
      <p style="margin-bottom:16px;">
        <strong><?= count($nomes) ?> times participantes:</strong>
        <?= implode(', ', array_map('htmlspecialchars', $nomes)) ?>
      </p>

      <form method="POST" action="../controller.php?action=gerar_tabela" style="margin-bottom:8px;">
        <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">

        <div style="margin-bottom:16px;">
          <label style="display:block;font-weight:600;margin-bottom:8px;">Formato da competição</label>
          <label style="display:inline-flex;align-items:center;margin-right:24px;cursor:pointer;">
            <input type="radio" name="formato" value="ida" checked style="margin-right:6px;">
            Somente Ida
          </label>
          <label style="display:inline-flex;align-items:center;cursor:pointer;">
            <input type="radio" name="formato" value="ida_volta" style="margin-right:6px;">
            Ida e Volta
          </label>
        </div>

        <button type="submit" class="btn-admin btn-success">&#9881; Gerar Tabela</button>
      </form>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php include '_footer.php'; ?>
