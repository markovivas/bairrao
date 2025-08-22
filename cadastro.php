<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Cadastrar Novo Jogo</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <?php
  include 'controller.php';
  ?>

  <h1>Cadastrar Novo Jogo</h1>

  <div class="cadastro-container">
    <?php if (isset($_GET['error'])): ?>
      <div class="error-message">
        <?php 
        if ($_GET['error'] == 1) echo "O time da casa e visitante não podem ser iguais!";
        if ($_GET['error'] == 2) echo "Senha incorreta!";
        if ($_GET['error'] == 4) echo "Data inválida!";
        if ($_GET['error'] == 5) echo "Todos os campos são obrigatórios!";
        if ($_GET['error'] == 6) echo "Rodada deve ser um número positivo!";
        if ($_GET['error'] == 7 && isset($_GET['detail'])) echo htmlspecialchars(urldecode($_GET['detail']));
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
      <div class="success-message">
        <?php if ($_GET['success'] == 2) echo "Jogo cadastrado com sucesso!"; ?>
      </div>
    <?php endif; ?>

    <form action="controller.php?action=save_next" method="post">
      <div class="jogo-container">
        <select name="time_casa" class="time-select" required>
          <option value="">Selecione o time da casa</option>
          <?php
          $sql = "SELECT nome FROM times ORDER BY nome";
          $result = $conn->query($sql);
          while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['nome']}'>{$row['nome']}</option>";
          }
          ?>
        </select>

        <span class="vs-text">X</span>

        <select name="time_visitante" class="time-select" required>
          <option value="">Selecione o time visitante</option>
          <?php
          $sql = "SELECT nome FROM times ORDER BY nome";
          $result = $conn->query($sql);
          while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['nome']}'>{$row['nome']}</option>";
          }
          ?>
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
          <div class="password-container">
            <input type="password" name="senha" class="password-input" required>
          </div>
        </div>

        <div class="btn-lancar-container">
          <button type="submit" class="btn-lancar">Cadastrar Jogo</button>
        </div>
      </div>
    </form>
  </div>

  <div class="voltar-link-container">
    <a href="index.php?view=tabela" class="voltar-link">Voltar para a Tabela</a>
  </div>

  <script>
    // Validação para evitar times iguais
    document.querySelector('form').addEventListener('submit', function(e) {
      const timeCasa = document.querySelector('select[name="time_casa"]').value;
      const timeVisitante = document.querySelector('select[name="time_visitante"]').value;
      
      if (timeCasa && timeVisitante && timeCasa === timeVisitante) {
        e.preventDefault();
        alert('O time da casa e o visitante não podem ser iguais!');
      }
    });
  </script>
</body>
</html>