<?php
include 'controller.php';
?>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Cadastrar Novo Jogo</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚽</text></svg>">
  <link rel="stylesheet" href="estilo.css">
</head>
<body>

  <div class="page-header">
    <h1>Cadastrar Jogo <small>Nova partida</small></h1>
  </div>

  <div class="cadastro-container">
    <?php if (isset($_GET['error'])): ?>
      <div class="error-message">
        <?php
        if ($_GET['error'] == 1) echo "O time da casa e visitante não podem ser iguais!";
        if ($_GET['error'] == 2) echo "Senha incorreta!";
        if ($_GET['error'] == 4) echo "Data inválida!";
        if ($_GET['error'] == 5) echo "Todos os campos são obrigatórios!";
        if ($_GET['error'] == 6) echo "Rodada deve ser um número positivo!";
        if ($_GET['error'] == 7) echo "Erro interno. Tente novamente.";
        if ($_GET['error'] == 8) echo "Nome do time não pode estar vazio!";
        if ($_GET['error'] == 9) echo "Este time possui jogos cadastrados e não pode ser removido.";
        if ($_GET['error'] == 'csrf') echo "Sessão expirada. Tente novamente.";
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <div class="success-message">
        <?php
        if ($_GET['success'] == 2) echo "Jogo cadastrado com sucesso!";
        if ($_GET['success'] == 3) echo "Time adicionado com sucesso!";
        if ($_GET['success'] == 4) echo "Time removido com sucesso!";
        ?>
      </div>
    <?php endif; ?>

    <form action="controller.php?action=save_next" method="post">
      <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
      <div class="jogo-container">
        <select name="time_casa" class="time-select" required>
          <option value="">Selecione o time da casa</option>
          <?php foreach (TimeModel::listarNomes($conn) as $nome): ?>
            <option value="<?= $nome ?>"><?= $nome ?></option>
          <?php endforeach; ?>
        </select>

        <span class="vs-text">X</span>

        <select name="time_visitante" class="time-select" required>
          <option value="">Selecione o time visitante</option>
          <?php foreach (TimeModel::listarNomes($conn) as $nome): ?>
            <option value="<?= $nome ?>"><?= $nome ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="senha-rodada-container">
        <div class="form-group">
          <label for="rodada">Rodada:</label>
          <input type="number" name="rodada" min="1" required class="gols-input">
        </div>

        <div class="form-group">
          <label for="data_jogo">Data e Hora do Jogo:</label>
          <input type="datetime-local" name="data_jogo" required>
        </div>

        <div class="form-group">
          <label for="senha">Senha de Administração:</label>
          <div class="password-wrapper">
            <input type="password" name="senha" class="password-input" required>
          </div>
        </div>

        <div class="btn-lancar-container">
          <button type="submit" class="btn-lancar">Cadastrar Jogo</button>
        </div>
      </div>
    </form>

    <hr style="margin: 32px 0; border: none; border-top: 1px solid #e9ecef;">

    <h2 style="text-align:center;color:#2d3436;font-size:1.3em;margin-bottom:20px;">Gerenciar Times</h2>

    <form action="controller.php?action=add_team" method="post" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
      <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
      <input type="text" name="nome" placeholder="Nome do novo time" required style="flex:1;min-width:200px;padding:12px 16px;border:2px solid #e0e0e0;border-radius:8px;font-size:1em;">
      <div class="password-wrapper" style="flex:0;min-width:auto;">
        <input type="password" name="senha" placeholder="Senha" required style="width:auto;padding:12px 16px;border:2px solid #e0e0e0;border-radius:8px;font-size:1em;">
      </div>
      <button type="submit" class="btn-lancar" style="padding:12px 24px;">Adicionar Time</button>
    </form>

    <div style="background:#f8f9fa;border-radius:12px;padding:16px;border:1px solid #e9ecef;">
      <h3 style="font-size:0.95em;color:#636e72;margin-bottom:12px;">Times cadastrados</h3>
      <div style="display:flex;flex-wrap:wrap;gap:8px;">
        <?php foreach (TimeModel::listarNomes($conn) as $nome): ?>
          <div style="display:flex;align-items:center;gap:6px;background:#fff;padding:6px 12px;border-radius:20px;border:1px solid #e0e0e0;font-size:0.9em;">
            <span><?= $nome ?></span>
            <form action="controller.php?action=delete_team" method="post" style="display:inline;" onsubmit="return confirm('Remover o time <?= $nome ?>?')">
              <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
              <input type="hidden" name="nome" value="<?= $nome ?>">
              <input type="password" name="senha" placeholder="senha" required style="width:70px;padding:4px 8px;border:1px solid #e0e0e0;border-radius:4px;font-size:0.8em;">
              <button type="submit" style="background:none;border:none;color:#e74c3c;cursor:pointer;font-size:1.1em;padding:2px;" title="Remover">&times;</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="voltar-link-container">
    <a href="index.php?view=tabela" class="voltar-link">Voltar para a Tabela</a>
  </div>

  <script>
    document.querySelector('form[action="controller.php?action=save_next"]').addEventListener('submit', function(e) {
      const timeCasa = this.querySelector('select[name="time_casa"]').value;
      const timeVisitante = this.querySelector('select[name="time_visitante"]').value;
      if (timeCasa && timeVisitante && timeCasa === timeVisitante) {
        e.preventDefault();
        alert('O time da casa e o visitante não podem ser iguais!');
      }
    });
  </script>
</body>
</html>