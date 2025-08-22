<?php
$conn = new mysqli("localhost", "root", "", "tabela");
if ($conn->connect_error) {
  die("Conexão falhou: " . $conn->connect_error);
}

// Definir senha de administração
define('ADMIN_PASSWORD', 'senha123');

function verificarSenha() {
    if (!isset($_POST['senha']) || $_POST['senha'] !== ADMIN_PASSWORD) {
        header("Location: index.php?view=tabela&error=2");
        exit();
    }
}

function displayTeams($conn) {
  $ordenar_por = isset($_GET['ordenar']) ? $conn->real_escape_string($_GET['ordenar']) : 'pontos';
  $ordem = isset($_GET['ordem']) && $_GET['ordem'] === 'asc' ? 'ASC' : 'DESC';
  $busca = isset($_GET['busca']) ? $conn->real_escape_string($_GET['busca']) : '';
  $filtro = $busca ? "WHERE nome LIKE '%$busca%'" : '';

  $sql = "SELECT * FROM times $filtro ORDER BY $ordenar_por $ordem, saldo_gols DESC, gols_pro DESC";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    $pos = 1;
    while ($row = $result->fetch_assoc()) {
      $nome_time = $row['nome'];
      $nome_arquivo = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome_time)));
      $caminho_escudo = "escudos/$nome_arquivo.png";
      if (!file_exists($caminho_escudo)) {
        $caminho_escudo = "escudos/escudo-generico.png";
      }

      echo "<tr>";
      echo "<td>{$pos}</td>";
      echo "<td><div style='display: flex; align-items: center; justify-content: center; gap: 10px;'>";
      echo "<img src='{$caminho_escudo}' class='escudo' alt='{$row['nome']}'>";
      echo $row['nome'];
      echo "</div></td>";
      echo "<td>{$row['pontos']}</td>";
      echo "<td>{$row['jogos']}</td>";
      echo "<td>{$row['vitorias']}</td>";
      echo "<td>{$row['empates']}</td>";
      echo "<td>{$row['derrotas']}</td>";
      echo "<td>{$row['gols_pro']}</td>";
      echo "<td>{$row['gols_contra']}</td>";
      echo "<td>{$row['saldo_gols']}</td>";
      echo "<td>{$row['aproveitamento']}</td>";
      echo "<td class='ultimos-jogos'>";
      foreach (explode(",", $row['ultimos_jogos']) as $res) {
        if ($res) echo "<span class='$res'></span>";
      }
      echo "</td></tr>";
      $pos++;
    }
  } else {
    echo "<tr><td colspan='12'>Nenhum dado encontrado.</td></tr>";
  }
}

function displayGames($conn, $rodada) {
  $sql = "SELECT * FROM historico_jogos WHERE rodada = $rodada ORDER BY data_jogo";
  $result = $conn->query($sql);
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $data = new DateTime($row['data_jogo']);
      $diaSemana = $data->format('l');
      $diaSemana = str_replace(
        ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
        $diaSemana
      );

      // Obter caminhos dos escudos
      $nome_casa = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $row['time_casa'])));
      $nome_visitante = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $row['time_visitante'])));
      $escudo_casa = file_exists("escudos/$nome_casa.png") ? "escudos/$nome_casa.png" : "escudos/escudo-generico.png";
      $escudo_visitante = file_exists("escudos/$nome_visitante.png") ? "escudos/$nome_visitante.png" : "escudos/escudo-generico.png";

      echo "<div class='jogo-item'>";
      echo "<div class='jogo-rodada'>Rodada {$row['rodada']}</div>";
      echo "<div class='jogo-times'>";
      echo "<div class='time-com-escudo'><img src='$escudo_casa' class='escudo-jogo' alt='{$row['time_casa']}'> {$row['time_casa']}</div>";
      if ($row['gols_casa'] !== null && $row['gols_visitante'] !== null) {
        echo "<div class='jogo-placar'>";
        echo "<span>{$row['gols_casa']}</span>";
        echo "<span>X</span>";
        echo "<span>{$row['gols_visitante']}</span>";
        echo "</div>";
      } else {
        echo "<div class='jogo-placar'><span>X</span></div>";
      }
      echo "<div class='time-com-escudo'><img src='$escudo_visitante' class='escudo-jogo' alt='{$row['time_visitante']}'> {$row['time_visitante']}</div>";
      echo "</div>";
      echo "<div class='jogo-info'>";
      echo "<span>{$data->format('d/m')} - {$diaSemana}</span>";
      echo "<span>{$data->format('H:i')}</span>";
      echo "</div>";
      echo "</div>";
    }
  } else {
    echo "<p>Nenhum jogo cadastrado para a rodada $rodada.</p>";
  }
}

function saveGameResult($conn) {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save') {
    verificarSenha();
    
    $jogo_id = (int)$_POST['jogo_id'];
    $gols_casa = (int)$_POST['gols_casa'];
    $gols_visitante = (int)$_POST['gols_visitante'];

    // Verificar se o jogo existe
    $sql = "SELECT time_casa, time_visitante, rodada FROM historico_jogos WHERE id = $jogo_id AND gols_casa IS NULL AND gols_visitante IS NULL";
    $result = $conn->query($sql);
    if ($result->num_rows === 0) {
      header("Location: lancar.php?error=3");
      exit();
    }
    $jogo = $result->fetch_assoc();
    $time_casa = $conn->real_escape_string($jogo['time_casa']);
    $time_visitante = $conn->real_escape_string($jogo['time_visitante']);
    $rodada = (int)$jogo['rodada'];

    // Atualizar o jogo com os resultados
    $sql_update = "UPDATE historico_jogos SET gols_casa = $gols_casa, gols_visitante = $gols_visitante WHERE id = $jogo_id";
    if (!$conn->query($sql_update)) {
      die("Erro ao registrar resultado: " . $conn->error);
    }

    // Função para atualizar estatísticas
    function atualizarEstatisticas($conn, $time, $gols_pro, $gols_contra, $resultado) {
      $pontos = 0;
      $vitorias = 0;
      $empates = 0;
      $derrotas = 0;
      switch ($resultado) {
        case 'v':
          $pontos = 3;
          $vitorias = 1;
          break;
        case 'e':
          $pontos = 1;
          $empates = 1;
          break;
        case 'd':
          $derrotas = 1;
          break;
      }
      $sql = "UPDATE times SET 
              pontos = pontos + $pontos,
              jogos = jogos + 1,
              vitorias = vitorias + $vitorias,
              empates = empates + $empates,
              derrotas = derrotas + $derrotas,
              gols_pro = gols_pro + $gols_pro,
              gols_contra = gols_contra + $gols_contra,
              saldo_gols = saldo_gols + ($gols_pro - $gols_contra),
              ultimos_jogos = CONCAT('$resultado,', IFNULL(ultimos_jogos, ''))
              WHERE nome = '$time'";
      if (!$conn->query($sql)) {
        die("Erro ao atualizar estatísticas do time $time: " . $conn->error);
      }
    }

    // Atualizar estatísticas com base no resultado
    if ($gols_casa > $gols_visitante) {
      atualizarEstatisticas($conn, $time_casa, $gols_casa, $gols_visitante, 'v');
      atualizarEstatisticas($conn, $time_visitante, $gols_visitante, $gols_casa, 'd');
    } elseif ($gols_casa == $gols_visitante) {
      atualizarEstatisticas($conn, $time_casa, $gols_casa, $gols_visitante, 'e');
      atualizarEstatisticas($conn, $time_visitante, $gols_visitante, $gols_casa, 'e');
    } else {
      atualizarEstatisticas($conn, $time_casa, $gols_casa, $gols_visitante, 'd');
      atualizarEstatisticas($conn, $time_visitante, $gols_visitante, $gols_casa, 'v');
    }

    // Atualizar aproveitamento
    $conn->query("UPDATE times SET aproveitamento = ROUND((pontos / (jogos * 3)) * 100) WHERE jogos > 0");
    header("Location: lancar.php?success=1");
    exit();
  }
}

function saveNextGame($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save_next') {
        verificarSenha();
        
        // Verifica se todos os campos necessários foram enviados
        if (!isset($_POST['time_casa']) || !isset($_POST['time_visitante']) || !isset($_POST['rodada']) || !isset($_POST['data_jogo'])) {
            header("Location: cadastro.php?error=5");
            exit();
        }

        $time_casa = $conn->real_escape_string($_POST['time_casa']);
        $time_visitante = $conn->real_escape_string($_POST['time_visitante']);
        $rodada = (int)$_POST['rodada'];
        $data_jogo = $conn->real_escape_string($_POST['data_jogo']);

        // Validações
        if ($time_casa === $time_visitante) {
            header("Location: cadastro.php?error=1");
            exit();
        }

        if ($rodada < 1) {
            header("Location: cadastro.php?error=6");
            exit();
        }

        if (!strtotime($data_jogo)) {
            header("Location: cadastro.php?error=4");
            exit();
        }

        // Formata a data para o formato MySQL
        $data_formatada = date('Y-m-d H:i:s', strtotime($data_jogo));

        $sql = "INSERT INTO historico_jogos (time_casa, time_visitante, rodada, data_jogo) 
               VALUES ('$time_casa', '$time_visitante', $rodada, '$data_formatada')";
        
        if ($conn->query($sql)) {
            header("Location: cadastro.php?success=2");
        } else {
            // Adiciona mensagem de erro mais detalhada
            $error_msg = urlencode("Erro ao cadastrar jogo: " . $conn->error);
            header("Location: cadastro.php?error=7&detail=" . $error_msg);
        }
        exit();
    }
}

// Handle game result saving
saveGameResult($conn);
// Handle next game saving
saveNextGame($conn);
?>