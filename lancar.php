<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Lançar Resultado de Jogo</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <?php
  include 'controller.php';
  ?>

  <h1>Lançar Resultado de Jogo</h1>

  <div class="cadastro-container">
    <?php if (isset($_GET['error'])): ?>
      <div class="error-message">
        <?php 
        if ($_GET['error'] == 2) echo "Senha incorreta!";
        if ($_GET['error'] == 3) echo "Jogo inválido ou já possui resultado!";
        if ($_GET['error'] == 5) echo "Todos os campos são obrigatórios!";
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <div class="success-message">
        <?php 
        if ($_GET['success'] == 1) echo "Resultado cadastrado com sucesso!";
        ?>
      </div>
    <?php endif; ?>

    <div id="form-resultado" class="cadastro-content active">
      <form action="controller.php?action=save" method="post">
        <div class="jogo-container">
          <select name="jogo_id" class="time-select" required onchange="updateGameDetails(this)">
            <option value="">Selecione um jogo cadastrado</option>
            <?php
            $sql = "SELECT id, time_casa, time_visitante, rodada FROM historico_jogos WHERE gols_casa IS NULL AND gols_visitante IS NULL ORDER BY rodada, data_jogo";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
              echo "<option value='{$row['id']}' data-casa='{$row['time_casa']}' data-visitante='{$row['time_visitante']}' data-rodada='{$row['rodada']}'>{$row['time_casa']} x {$row['time_visitante']} (Rodada {$row['rodada']})</option>";
            }
            ?>
          </select>
          <input type="number" name="gols_casa" class="gols-input" min="0" value="0" required>
          <span class="vs-text">X</span>
          <input type="number" name="gols_visitante" class="gols-input" min="0" value="0" required>
          <div id="game-details" style="font-size: 0.9em; color: #666;"></div>
        </div>

        <div class="senha-rodada-container">
          <div class="form-group">
            <label for="senha">Senha de Administração:</label>
            <div class="password-container">
              <input type="password" name="senha" class="password-input" required>
            </div>
          </div>
          <div class="btn-lancar-container">
            <button type="submit" class="btn-lancar">Lançar Resultado</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="voltar-link-container">
    <a href="index.php?view=tabela" class="voltar-link">Voltar para a Tabela</a>
  </div>

  <script>
    function updateGameDetails(select) {
      const selectedOption = select.options[select.selectedIndex];
      const casa = selectedOption.getAttribute('data-casa');
      const visitante = selectedOption.getAttribute('data-visitante');
      const rodada = selectedOption.getAttribute('data-rodada');
      const detailsDiv = document.getElementById('game-details');
      if (casa && visitante && rodada) {
        detailsDiv.innerText = `Jogo: ${casa} x ${visitante} (Rodada ${rodada})`;
      } else {
        detailsDiv.innerText = '';
      }
    }
  </script>
</body>
</html>