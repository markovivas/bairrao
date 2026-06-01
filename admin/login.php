<?php
require_once __DIR__ . '/../controller.php';

if (!empty($_SESSION['admin_logged_in'])) {
  header("Location: index.php");
  exit();
}

$erro = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <link rel="stylesheet" href="../estilo.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚽</text></svg>">
</head>
<body>
  <div class="page-header" style="margin-bottom:20px;">
    <h1>Painel Administrativo</h1>
  </div>
  <div style="max-width:400px;margin:0 auto;padding:0 12px;">
    <div class="admin-card" style="text-align:left;">
      <form method="POST" action="../controller.php?action=admin_login">
        <label style="display:block;margin-bottom:8px;font-weight:600;">Senha de Administrador</label>
        <input type="password" name="senha" required style="width:100%;padding:10px;border:2px solid #e0e0e0;border-radius:8px;box-sizing:border-box;margin-bottom:12px;">
        <button type="submit" class="btn-admin btn-success" style="width:100%;padding:10px;font-size:1em;">Entrar</button>
      </form>
      <?php if ($erro === '1'): ?>
        <p style="color:#e74c3c;margin-top:12px;">Senha incorreta.</p>
      <?php elseif ($erro === 'csrf'): ?>
        <p style="color:#e74c3c;margin-top:12px;">Sessão inválida. Tente novamente.</p>
      <?php endif; ?>
    </div>
    <p style="text-align:center;margin-top:16px;"><a href="../index.php">← Voltar para a tabela</a></p>
  </div>
</body>
</html>
