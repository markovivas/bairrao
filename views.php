<?php
function exibirTabela($conn) {
  $ordenar_por = $_GET['ordenar'] ?? 'pontos';
  $ordem = (isset($_GET['ordem']) && $_GET['ordem'] === 'asc') ? 'ASC' : 'DESC';
  $busca = $_GET['busca'] ?? '';

  $times = TimeModel::listar($conn, $ordenar_por, $ordem, $busca);

  if (count($times) === 0) {
    echo "<tr><td colspan='12'>Nenhum time encontrado.</td></tr>";
    return;
  }

  $total = count($times);
  $pos = 1;
  foreach ($times as $row) {
    $zona = '';
    if ($pos <= 4) $zona = 'zona-g4';
    elseif ($pos <= 6) $zona = 'zona-sudamericana';
    elseif ($pos > $total - 4) $zona = 'zona-z4';

    echo "<tr class='$zona'>";
    echo "<td><span class='posicao'>$pos</span></td>";
    echo "<td class='time-cell'>";
    echo "<img src='{$row['escudo']}' class='escudo' alt='{$row['nome']}'>";
    echo $row['nome'];
    echo "</td>";
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
      $titulo = '';
      if ($res === 'v') $titulo = 'Vitória';
      elseif ($res === 'e') $titulo = 'Empate';
      elseif ($res === 'd') $titulo = 'Derrota';
      if ($res) echo "<span class='$res' title='$titulo'></span>";
    }
    echo "</td></tr>";
    $pos++;
  }
}

function exibirJogos($conn, $rodada) {
  $jogos = JogoModel::listarPorRodada($conn, $rodada);

  if (count($jogos) === 0) {
    echo "<p>Nenhum jogo cadastrado para a rodada $rodada.</p>";
    return;
  }

  foreach ($jogos as $row) {
    $data = new DateTime($row['data_jogo']);
    $dia = str_replace(
      ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
      ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
      $data->format('l')
    );

    $nome_casa = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $row['time_casa'])));
    $nome_visitante = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $row['time_visitante'])));
    $escudo_casa = file_exists("escudos/$nome_casa.png") ? "escudos/$nome_casa.png" : "escudos/escudo-generico.png";
    $escudo_visitante = file_exists("escudos/$nome_visitante.png") ? "escudos/$nome_visitante.png" : "escudos/escudo-generico.png";

    echo "<div class='jogo-item'>";
    echo "<div class='jogo-rodada'>Rodada {$row['rodada']}</div>";
    echo "<div class='jogo-times'>";
    echo "<div class='time-com-escudo'><img src='$escudo_casa' class='escudo-jogo' alt='{$row['time_casa']}'> {$row['time_casa']}</div>";

    if ($row['gols_casa'] !== null && $row['gols_visitante'] !== null) {
      echo "<div class='jogo-placar'><span>{$row['gols_casa']}</span><span class='x'>X</span><span>{$row['gols_visitante']}</span></div>";
    } else {
      echo "<div class='jogo-placar-sem-resultado'>a realizar</div>";
    }

    echo "<div class='time-com-escudo'><img src='$escudo_visitante' class='escudo-jogo' alt='{$row['time_visitante']}'> {$row['time_visitante']}</div>";
    echo "</div>";
    echo "<div class='jogo-info'><span>{$data->format('d/m')} - {$dia}</span><span>{$data->format('H:i')}</span></div>";
    echo "</div>";
  }
}
