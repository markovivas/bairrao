<?php
include 'controller.php';
$aba = $_GET['aba'] ?? 'lancar';
?>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Lançar Resultado de Jogo</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚽</text></svg>">
  <link rel="stylesheet" href="estilo.css">
</head>
<body>

  <div class="page-header">
    <h1>Lançar Resultado <small>Registrar placar</small></h1>
  </div>

  <div class="cadastro-container">
    <?php if (isset($_GET['error'])): ?>
      <div class="error-message">
        <?php
        if ($_GET['error'] == 2) echo "Senha incorreta!";
        if ($_GET['error'] == 3) echo "Jogo inválido ou já possui resultado!";
        if ($_GET['error'] == 5) echo "Todos os campos são obrigatórios!";
        if ($_GET['error'] == 7) echo "Erro interno. Tente novamente.";
        if ($_GET['error'] == 'csrf') echo "Sessão expirada. Tente novamente.";
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <div class="success-message">
        <?php
        if ($_GET['success'] == 1) echo "Resultado cadastrado com sucesso!";
        if ($_GET['success'] == 2) echo "Resultado editado com sucesso!";
        ?>
      </div>
    <?php endif; ?>

    <div class="cadastro-tabs">
      <a href="?aba=lancar" class="cadastro-tab <?= $aba === 'lancar' ? 'active' : '' ?>">Lançar Resultado</a>
      <a href="?aba=editar" class="cadastro-tab <?= $aba === 'editar' ? 'active' : '' ?>">Editar Resultado</a>
    </div>

    <?php if ($aba === 'lancar'): ?>
    <div id="form-lancar">
      <form action="controller.php?action=save" method="post">
        <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
        <div class="jogo-container">
          <select name="jogo_id" class="time-select" required onchange="updateGameDetails(this)">
            <option value="">Selecione um jogo cadastrado</option>
            <?php foreach (JogoModel::listarSemResultado($conn) as $row): ?>
              <option value="<?= $row['id'] ?>" data-casa="<?= $row['time_casa'] ?>" data-visitante="<?= $row['time_visitante'] ?>" data-rodada="<?= $row['rodada'] ?>"><?= $row['time_casa'] ?> x <?= $row['time_visitante'] ?> (Rodada <?= $row['rodada'] ?>)</option>
            <?php endforeach; ?>
          </select>
          <input type="number" name="gols_casa" class="gols-input" min="0" value="0" required>
          <span class="vs-text">X</span>
          <input type="number" name="gols_visitante" class="gols-input" min="0" value="0" required>
          <div id="game-details"></div>
        </div>
        <div class="senha-rodada-container">
          <div class="form-group">
            <label for="senha">Senha de Administração:</label>
            <div class="password-wrapper">
              <input type="password" name="senha" class="password-input" required>
            </div>
          </div>
          <div class="btn-lancar-container">
            <button type="submit" class="btn-lancar">Lançar Resultado</button>
          </div>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <?php if ($aba === 'editar'): ?>
    <div id="form-editar">
      <form action="controller.php?action=edit" method="post">
        <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
        <div class="jogo-container">
          <select name="jogo_id" class="time-select" required onchange="preencherPlacar(this)">
            <option value="">Selecione um jogo com resultado</option>
            <?php foreach (JogoModel::listarComResultado($conn) as $row): ?>
              <option value="<?= $row['id'] ?>" data-casa="<?= $row['time_casa'] ?>" data-visitante="<?= $row['time_visitante'] ?>" data-gols-casa="<?= $row['gols_casa'] ?>" data-gols-visitante="<?= $row['gols_visitante'] ?>" data-rodada="<?= $row['rodada'] ?>"><?= $row['time_casa'] ?> <?= $row['gols_casa'] ?> x <?= $row['gols_visitante'] ?> <?= $row['time_visitante'] ?> (Rodada <?= $row['rodada'] ?>)</option>
            <?php endforeach; ?>
          </select>
          <input type="number" name="gols_casa" id="edit_gols_casa" class="gols-input" min="0" value="0" required>
          <span class="vs-text">X</span>
          <input type="number" name="gols_visitante" id="edit_gols_visitante" class="gols-input" min="0" value="0" required>
          <div id="edit-game-details" style="width:100%;text-align:center;font-size:0.85em;color:#636e72;margin-top:8px;"></div>
        </div>
        <div class="senha-rodada-container">
          <div class="form-group">
            <label for="senha">Senha de Administração:</label>
            <div class="password-wrapper">
              <input type="password" name="senha" class="password-input" required>
            </div>
          </div>
          <div class="btn-lancar-container">
            <button type="submit" class="btn-lancar">Salvar Alteração</button>
          </div>
        </div>
      </form>
    </div>
    <?php endif; ?>
  </div>

  <div class="voltar-link-container">
    <a href="index.php?view=tabela" class="voltar-link">Voltar para a Tabela</a>
  </div>

  <script>
    function updateGameDetails(select) {
      const opt = select.options[select.selectedIndex];
      const el = document.getElementById('game-details');
      const casa = opt.getAttribute('data-casa');
      const visit = opt.getAttribute('data-visitante');
      const rod = opt.getAttribute('data-rodada');
      el.innerText = (casa && visit && rod) ? `Jogo: ${casa} x ${visit} (Rodada ${rod})` : '';
    }

    function preencherPlacar(select) {
      const opt = select.options[select.selectedIndex];
      const el = document.getElementById('edit-game-details');
      const casa = opt.getAttribute('data-casa');
      const visit = opt.getAttribute('data-visitante');
      const rod = opt.getAttribute('data-rodada');
      const gc = opt.getAttribute('data-gols-casa');
      const gv = opt.getAttribute('data-gols-visitante');
      if (casa && visit) {
        document.getElementById('edit_gols_casa').value = gc;
        document.getElementById('edit_gols_visitante').value = gv;
        el.innerText = `Editando: ${casa} ${gc} x ${gv} ${visit} (Rodada ${rod})`;
      } else {
        el.innerText = '';
      }
    }
  </script>
</body>
</html>
