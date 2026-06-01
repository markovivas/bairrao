<?php
require_once __DIR__ . '/../controller.php';
requerAdmin();

$times = TimeModel::listar($conn, 'nome', 'ASC', '');
$jogos_pendentes = JogoModel::listarSemResultado($conn);
$jogos_finalizados = JogoModel::listarComResultado($conn);
$ultima_rodada = JogoModel::ultimaRodada($conn);
$proxima_rodada = $ultima_rodada;

// Buscar jogo para editar
$editar_jogo = null;
if (isset($_GET['editar'])) {
  $editar_jogo = JogoModel::buscarCompleto($conn, (int)$_GET['editar']);
}

$mensagens = [
  'success' => ['1' => 'Resultado salvo com sucesso!', '2' => 'Jogo cadastrado com sucesso!', '3' => 'Jogo editado com sucesso!'],
  'error' => ['1' => 'Times devem ser diferentes.', '3' => 'Jogo não encontrado.', '4' => 'Data inválida.', '5' => 'Preencha todos os campos.', '6' => 'Rodada inválida.', '7' => 'Erro ao salvar.', '8' => 'Jogo já possui resultado.']
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
  <h2><a href="#" onclick="document.getElementById('cadastro-section').style.display='block';this.style.display='none';return false;" style="text-decoration:none;color:inherit;">+ Cadastrar Novo Jogo</a></h2>
  <div id="cadastro-section" style="display:<?= isset($_GET['cadastrar']) ? 'block' : 'none' ?>">
    <form method="POST" action="../controller.php?action=save_next" class="inline-form">
      <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
      <select name="time_casa" required style="padding:8px;border:2px solid #e0e0e0;border-radius:6px;">
        <option value="">Casa</option>
        <?php foreach ($times as $t): ?>
          <option value="<?= htmlspecialchars($t['nome']) ?>"><?= htmlspecialchars($t['nome']) ?></option>
        <?php endforeach; ?>
      </select>
      <span style="font-weight:700;">×</span>
      <select name="time_visitante" required style="padding:8px;border:2px solid #e0e0e0;border-radius:6px;">
        <option value="">Visitante</option>
        <?php foreach ($times as $t): ?>
          <option value="<?= htmlspecialchars($t['nome']) ?>"><?= htmlspecialchars($t['nome']) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="number" name="rodada" placeholder="Rodada" value="<?= $proxima_rodada ?>" min="1" required style="width:80px;padding:8px;border:2px solid #e0e0e0;border-radius:6px;">
      <input type="datetime-local" name="data_jogo" required style="padding:8px;border:2px solid #e0e0e0;border-radius:6px;">
      <button type="submit" class="btn-admin btn-success">Cadastrar</button>
    </form>
  </div>
</div>

<div class="admin-section">
  <h2>Jogos Pendentes (sem resultado)</h2>
  <?php if (empty($jogos_pendentes)): ?>
    <p style="color:#636e72;">Nenhum jogo pendente.</p>
  <?php else: ?>
    <?php foreach ($jogos_pendentes as $j): ?>
      <div class="jogo-row">
        <div class="info">
          <strong><?= htmlspecialchars($j['time_casa']) ?></strong> × <strong><?= htmlspecialchars($j['time_visitante']) ?></strong>
          <br><small style="color:#636e72;">Rodada <?= $j['rodada'] ?> — <?= date('d/m/Y H:i', strtotime($j['data_jogo'])) ?></small>
        </div>
        <div class="acoes">
          <form method="POST" action="../controller.php?action=save" class="inline-form">
            <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
            <input type="hidden" name="jogo_id" value="<?= $j['id'] ?>">
            <input type="number" name="gols_casa" min="0" required style="width:50px;">
            <span style="font-weight:700;">×</span>
            <input type="number" name="gols_visitante" min="0" required style="width:50px;">
            <button type="submit" class="btn-admin btn-success">Lançar</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="admin-section">
  <h2>Jogos com Resultado</h2>
  <?php if (empty($jogos_finalizados)): ?>
    <p style="color:#636e72;">Nenhum resultado lançado.</p>
  <?php else: ?>
    <?php foreach ($jogos_finalizados as $j): ?>
      <div class="jogo-row">
        <div class="info">
          <strong><?= htmlspecialchars($j['time_casa']) ?></strong> <?= (int)$j['gols_casa'] ?> × <?= (int)$j['gols_visitante'] ?> <strong><?= htmlspecialchars($j['time_visitante']) ?></strong>
          <br><small style="color:#636e72;">Rodada <?= $j['rodada'] ?></small>
        </div>
        <div class="acoes">
          <?php if ($editar_jogo && $editar_jogo['id'] == $j['id']): ?>
            <form method="POST" action="../controller.php?action=edit" class="inline-form">
              <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
              <input type="hidden" name="jogo_id" value="<?= $j['id'] ?>">
              <input type="number" name="gols_casa" min="0" value="<?= (int)$j['gols_casa'] ?>" required style="width:50px;">
              <span style="font-weight:700;">×</span>
              <input type="number" name="gols_visitante" min="0" value="<?= (int)$j['gols_visitante'] ?>" required style="width:50px;">
              <button type="submit" class="btn-admin btn-warning">Salvar</button>
              <a href="jogos.php" style="text-decoration:none;color:#636e72;">Cancelar</a>
            </form>
          <?php else: ?>
            <a href="jogos.php?editar=<?= $j['id'] ?>" class="btn-admin btn-warning">Editar</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php include '_footer.php'; ?>
