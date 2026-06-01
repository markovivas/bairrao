<?php
require_once __DIR__ . '/../controller.php';
$admin_nome = ConfigModel::get($conn, 'campeonato_nome');
$pagina = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Painel Admin — <?= $admin_nome ?></title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚽</text></svg>">
  <link rel="stylesheet" href="../estilo.css">
  <style>
    .admin-nav { display:flex; gap:4px; margin-bottom:24px; flex-wrap:wrap; background:#fff; border-radius:12px; padding:12px 16px; box-shadow:0 2px 12px rgba(0,0,0,0.06); }
    .admin-nav a { padding:8px 16px; border-radius:8px; text-decoration:none; font-weight:600; font-size:0.9em; color:#2d3436; transition:all 0.2s; }
    .admin-nav a:hover { background:#f0f2f5; }
    .admin-nav a.active { background:#006341; color:#fff; }
    .admin-nav a.sair { margin-left:auto; color:#e74c3c; }
    .admin-nav a.sair:hover { background:#fff5f5; }
    .admin-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:16px; margin-bottom:24px; }
    .admin-card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 12px rgba(0,0,0,0.06); text-align:center; }
    .admin-card .numero { font-size:2em; font-weight:800; color:#006341; }
    .admin-card .rotulo { font-size:0.85em; color:#636e72; margin-top:4px; }
    .admin-section { background:#fff; border-radius:12px; padding:24px; box-shadow:0 2px 12px rgba(0,0,0,0.06); margin-bottom:20px; }
    .admin-section h2 { font-size:1.2em; color:#2d3436; margin-bottom:16px; padding-bottom:8px; border-bottom:2px solid #f0f2f5; }
    .jogo-row { display:flex; align-items:center; gap:12px; padding:12px; border-bottom:1px solid #f0f2f5; flex-wrap:wrap; }
    .jogo-row:last-child { border-bottom:none; }
    .jogo-row .info { flex:1; min-width:200px; }
    .jogo-row .acoes { display:flex; gap:8px; align-items:center; }
    .btn-admin { padding:6px 14px; border-radius:6px; border:none; cursor:pointer; font-weight:600; font-size:0.85em; transition:all 0.2s; }
    .btn-admin:hover { transform:translateY(-1px); }
    .btn-success { background:#2ecc71; color:#fff; }
    .btn-success:hover { background:#27ae60; }
    .btn-warning { background:#f39c12; color:#fff; }
    .btn-warning:hover { background:#e67e22; }
    .btn-danger { background:#e74c3c; color:#fff; }
    .btn-danger:hover { background:#c0392b; }
    .btn-outline { background:transparent; border:2px solid #e0e0e0; color:#2d3436; }
    .btn-outline:hover { border-color:#006341; color:#006341; }
    .inline-form { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .inline-form input[type="number"] { width:60px; padding:6px 8px; border:2px solid #e0e0e0; border-radius:6px; text-align:center; font-weight:700; }
    .inline-form input[type="number"]:focus { border-color:#00a86b; outline:none; }
  </style>
</head>
<body>
  <div class="page-header" style="margin-bottom:20px;">
    <h1>Painel Administrativo <small><?= $admin_nome ?></small></h1>
  </div>

  <div style="max-width:960px;margin:0 auto;padding:0 12px;">

  <nav class="admin-nav">
    <a href="index.php" class="<?= $pagina === 'index.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="jogos.php" class="<?= $pagina === 'jogos.php' ? 'active' : '' ?>">Jogos</a>
    <a href="times.php" class="<?= $pagina === 'times.php' ? 'active' : '' ?>">Times</a>
    <a href="configuracoes.php" class="<?= $pagina === 'configuracoes.php' ? 'active' : '' ?>">Configurações</a>
    <a href="exportar.php" class="<?= $pagina === 'exportar.php' ? 'active' : '' ?>">Exportar</a>
    <a href="gerar.php" class="<?= $pagina === 'gerar.php' ? 'active' : '' ?>">Gerar Tabela</a>
    <a href="../index.php" class="sair" target="_blank">Ver Site</a>
    <a href="../controller.php?action=admin_logout" class="sair">Sair</a>
  </nav>
