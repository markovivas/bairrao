<?php
require_once __DIR__ . '/../controller.php';
requerAdmin();

$config = ConfigModel::getAll($conn);
?>
<?php include '_header.php'; ?>

<?php $v = $_GET['success'] ?? ''; if ($v === '1'): ?>
  <div style="padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;background:#d4edda;color:#155724;">
    Configurações salvas com sucesso!
  </div>
<?php endif; ?>

<div class="admin-section">
  <h2>Configurações do Campeonato</h2>
  <form method="POST" action="../controller.php?action=save_config">
    <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
    <div style="margin-bottom:16px;">
      <label style="display:block;font-weight:600;margin-bottom:6px;">Nome do Campeonato</label>
      <input type="text" name="campeonato_nome" value="<?= htmlspecialchars($config['campeonato_nome'] ?? 'Brasileirão') ?>" required style="width:100%;max-width:400px;padding:10px;border:2px solid #e0e0e0;border-radius:8px;box-sizing:border-box;">
    </div>
    <div style="margin-bottom:16px;">
      <label style="display:block;font-weight:600;margin-bottom:6px;">Descrição</label>
      <input type="text" name="campeonato_descricao" value="<?= htmlspecialchars($config['campeonato_descricao'] ?? 'Série A') ?>" required style="width:100%;max-width:400px;padding:10px;border:2px solid #e0e0e0;border-radius:8px;box-sizing:border-box;">
    </div>
    <button type="submit" class="btn-admin btn-success">Salvar</button>
  </form>
</div>

<?php include '_footer.php'; ?>
