<?php
class TimeModel {
  private static function escudoPath($nome_time) {
    $nome_arquivo = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome_time)));
    $caminho = "escudos/$nome_arquivo.png";
    return file_exists($caminho) ? $caminho : "escudos/escudo-generico.png";
  }

  static function listar($conn, $ordenar_por, $ordem, $busca) {
    $colunas_validas = ['pontos', 'jogos', 'vitorias', 'empates', 'derrotas',
                        'gols_pro', 'gols_contra', 'saldo_gols', 'aproveitamento'];
    $ordenar_por = in_array($ordenar_por, $colunas_validas, true) ? $ordenar_por : 'pontos';
    $ordem = $ordem === 'ASC' ? 'ASC' : 'DESC';

    if ($busca !== '') {
      $stmt = $conn->prepare("SELECT * FROM times WHERE nome LIKE ? ORDER BY $ordenar_por $ordem, saldo_gols DESC, gols_pro DESC");
      $like = "%$busca%";
      $stmt->bind_param("s", $like);
    } else {
      $stmt = $conn->prepare("SELECT * FROM times ORDER BY $ordenar_por $ordem, saldo_gols DESC, gols_pro DESC");
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $times = [];
    while ($row = $result->fetch_assoc()) {
      $row['escudo'] = self::escudoPath($row['nome']);
      $times[] = $row;
    }
    $stmt->close();
    return $times;
  }

  static function atualizarEstatisticas($conn, $time, $gols_pro, $gols_contra, $resultado, $rodada) {
    $mapa = ['v' => [3, 1, 0, 0], 'e' => [1, 0, 1, 0], 'd' => [0, 0, 0, 1]];
    $dados = $mapa[$resultado] ?? [0, 0, 0, 0];
    [$pontos, $vitorias, $empates, $derrotas] = $dados;

    $stmt = $conn->prepare("UPDATE times SET
      pontos = pontos + ?,
      jogos = jogos + 1,
      vitorias = vitorias + ?,
      empates = empates + ?,
      derrotas = derrotas + ?,
      gols_pro = gols_pro + ?,
      gols_contra = gols_contra + ?,
      saldo_gols = saldo_gols + (? - ?),
      ultimos_jogos = CONCAT(?, IFNULL(ultimos_jogos, ''))
      WHERE nome = ?");
    $historico = $resultado . ',';
    $stmt->bind_param("iiiiiiiiss", $pontos, $vitorias, $empates, $derrotas,
                      $gols_pro, $gols_contra, $gols_pro, $gols_contra, $historico, $time);
    $stmt->execute();
    $stmt->close();
  }

  static function recalcularAproveitamento($conn) {
    $conn->query("UPDATE times SET aproveitamento = ROUND((pontos / (jogos * 3)) * 100) WHERE jogos > 0");
  }

  static function listarNomes($conn) {
    $result = $conn->query("SELECT nome FROM times ORDER BY nome");
    $nomes = [];
    while ($row = $result->fetch_assoc()) {
      $nomes[] = $row['nome'];
    }
    return $nomes;
  }

  static function reverterEstatisticas($conn, $time, $gols_pro, $gols_contra, $resultado) {
    $mapa = ['v' => [3, 1, 0, 0], 'e' => [1, 0, 1, 0], 'd' => [0, 0, 0, 1]];
    $dados = $mapa[$resultado] ?? [0, 0, 0, 0];
    [$pontos, $vitorias, $empates, $derrotas] = $dados;

    $stmt = $conn->prepare("UPDATE times SET
      pontos = GREATEST(pontos - ?, 0),
      jogos = GREATEST(jogos - 1, 0),
      vitorias = GREATEST(vitorias - ?, 0),
      empates = GREATEST(empates - ?, 0),
      derrotas = GREATEST(derrotas - ?, 0),
      gols_pro = GREATEST(gols_pro - ?, 0),
      gols_contra = GREATEST(gols_contra - ?, 0),
      saldo_gols = saldo_gols - (? - ?)
      WHERE nome = ?");
    $stmt->bind_param("iiiiiiiis", $pontos, $vitorias, $empates, $derrotas,
                      $gols_pro, $gols_contra, $gols_pro, $gols_contra, $time);
    $stmt->execute();
    $stmt->close();
  }

  static function reconstruirUltimosJogos($conn, $time) {
    $stmt = $conn->prepare("SELECT time_casa, time_visitante, gols_casa, gols_visitante
      FROM historico_jogos
      WHERE (time_casa = ? OR time_visitante = ?) AND gols_casa IS NOT NULL
      ORDER BY data_jogo DESC, id DESC LIMIT 10");
    $stmt->bind_param("ss", $time, $time);
    $stmt->execute();
    $result = $stmt->get_result();

    $res = [];
    while ($row = $result->fetch_assoc()) {
      if ($row['time_casa'] === $time) {
        $gp = $row['gols_casa']; $gc = $row['gols_visitante'];
      } else {
        $gp = $row['gols_visitante']; $gc = $row['gols_casa'];
      }
      if ($gp > $gc) $res[] = 'v';
      elseif ($gp == $gc) $res[] = 'e';
      else $res[] = 'd';
    }
    $stmt->close();

    $csv = implode(',', $res);
    $stmt2 = $conn->prepare("UPDATE times SET ultimos_jogos = ? WHERE nome = ?");
    $stmt2->bind_param("ss", $csv, $time);
    $stmt2->execute();
    $stmt2->close();
  }

  static function adicionar($conn, $nome) {
    $stmt = $conn->prepare("INSERT INTO times (nome, pontos, jogos, vitorias, empates, derrotas, gols_pro, gols_contra, saldo_gols, aproveitamento, ultimos_jogos) VALUES (?, 0, 0, 0, 0, 0, 0, 0, 0, 0, '')");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $ok = $stmt->affected_rows > 0;
    $erro = $stmt->error;
    $stmt->close();
    return $ok ? true : $erro;
  }

  static function remover($conn, $nome) {
    $stmt = $conn->prepare("DELETE FROM times WHERE nome = ?");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $ok = $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
  }

  static function existeEmJogo($conn, $nome) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM historico_jogos WHERE time_casa = ? OR time_visitante = ?");
    $stmt->bind_param("ss", $nome, $nome);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'] > 0;
  }
}

class JogoModel {
  static function listarPorRodada($conn, $rodada) {
    $stmt = $conn->prepare("SELECT * FROM historico_jogos WHERE rodada = ? ORDER BY data_jogo");
    $stmt->bind_param("i", $rodada);
    $stmt->execute();
    $result = $stmt->get_result();
    $jogos = [];
    while ($row = $result->fetch_assoc()) {
      $jogos[] = $row;
    }
    $stmt->close();
    return $jogos;
  }

  static function buscarParaResultado($conn, $jogo_id) {
    $stmt = $conn->prepare("SELECT time_casa, time_visitante, rodada FROM historico_jogos WHERE id = ? AND gols_casa IS NULL AND gols_visitante IS NULL");
    $stmt->bind_param("i", $jogo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $jogo = $result->fetch_assoc();
    $stmt->close();
    return $jogo;
  }

  static function salvarResultado($conn, $jogo_id, $gols_casa, $gols_visitante) {
    $stmt = $conn->prepare("UPDATE historico_jogos SET gols_casa = ?, gols_visitante = ? WHERE id = ?");
    $stmt->bind_param("iii", $gols_casa, $gols_visitante, $jogo_id);
    $stmt->execute();
    $afetadas = $stmt->affected_rows;
    $stmt->close();
    return $afetadas > 0;
  }

  static function cadastrar($conn, $time_casa, $time_visitante, $rodada, $data_jogo) {
    $stmt = $conn->prepare("INSERT INTO historico_jogos (time_casa, time_visitante, rodada, data_jogo) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $time_casa, $time_visitante, $rodada, $data_jogo);
    $stmt->execute();
    $sucesso = $stmt->affected_rows > 0;
    $erro = $stmt->error;
    $stmt->close();
    return $sucesso ?: $erro;
  }

  static function listarSemResultado($conn) {
    $result = $conn->query("SELECT id, time_casa, time_visitante, rodada, data_jogo FROM historico_jogos WHERE gols_casa IS NULL AND gols_visitante IS NULL ORDER BY rodada, data_jogo");
    $jogos = [];
    while ($row = $result->fetch_assoc()) {
      $jogos[] = $row;
    }
    return $jogos;
  }

  static function listarComResultado($conn) {
    $result = $conn->query("SELECT id, time_casa, time_visitante, rodada, gols_casa, gols_visitante, data_jogo FROM historico_jogos WHERE gols_casa IS NOT NULL AND gols_visitante IS NOT NULL ORDER BY rodada DESC, data_jogo DESC");
    $jogos = [];
    while ($row = $result->fetch_assoc()) {
      $jogos[] = $row;
    }
    return $jogos;
  }

  static function buscarCompleto($conn, $jogo_id) {
    $stmt = $conn->prepare("SELECT * FROM historico_jogos WHERE id = ?");
    $stmt->bind_param("i", $jogo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $jogo = $result->fetch_assoc();
    $stmt->close();
    return $jogo;
  }

  static function limparResultado($conn, $jogo_id) {
    $stmt = $conn->prepare("UPDATE historico_jogos SET gols_casa = NULL, gols_visitante = NULL WHERE id = ?");
    $stmt->bind_param("i", $jogo_id);
    $stmt->execute();
    $ok = $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
  }

  static function atualizarData($conn, $jogo_id, $data) {
    $stmt = $conn->prepare("UPDATE historico_jogos SET data_jogo = ? WHERE id = ?");
    $stmt->bind_param("si", $data, $jogo_id);
    $stmt->execute();
    $ok = $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
  }

  static function ultimaRodada($conn) {
    $result = $conn->query("SELECT MAX(rodada) as ultima FROM historico_jogos");
    $row = $result->fetch_assoc();
    return (int)($row['ultima'] ?? 1);
  }

  static function proximaRodadaPendente($conn) {
    $result = $conn->query("SELECT MIN(rodada) as rodada FROM historico_jogos WHERE gols_casa IS NULL");
    $row = $result->fetch_assoc();
    return $row['rodada'] !== null ? (int)$row['rodada'] : self::ultimaRodada($conn);
  }

  static function totalJogos($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM historico_jogos");
    return (int)$result->fetch_assoc()['total'];
  }

  static function totalComResultado($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM historico_jogos WHERE gols_casa IS NOT NULL");
    return (int)$result->fetch_assoc()['total'];
  }

  static function timesSorteados($nomes) {
    shuffle($nomes);
    return $nomes;
  }

  static function gerarTabela($conn, $nomes, $formato) {
    $n = count($nomes);
    if ($n < 2) return [];

    if ($n % 2 != 0) {
      $nomes[] = null;
      $n++;
    }

    $times = $nomes;
    $totalRounds = $n - 1;
    $rounds = [];

    for ($r = 0; $r < $totalRounds; $r++) {
      $round = [];
      for ($i = 0; $i < $n / 2; $i++) {
        $casa = $times[$i];
        $fora = $times[$n - 1 - $i];
        if ($casa !== null && $fora !== null) {
          if (rand(0, 1) === 0) {
            $round[] = ['home' => $casa, 'away' => $fora];
          } else {
            $round[] = ['home' => $fora, 'away' => $casa];
          }
        }
      }
      $rounds[] = $round;

      $fixo = $times[0];
      $resto = array_slice($times, 1);
      $ultimo = array_pop($resto);
      array_unshift($resto, $ultimo);
      $times = array_merge([$fixo], $resto);
    }

    shuffle($rounds);

    if ($formato === 'ida_volta') {
      $volta = [];
      foreach ($rounds as $round) {
        $r = [];
        foreach ($round as $m) {
          $r[] = ['home' => $m['away'], 'away' => $m['home']];
        }
        $volta[] = $r;
      }
      $rounds = array_merge($rounds, $volta);
    }

    return $rounds;
  }

  static function limparEZerar($conn) {
    $conn->query("TRUNCATE TABLE historico_jogos");
    $conn->query("UPDATE times SET pontos=0, jogos=0, vitorias=0, empates=0, derrotas=0, gols_pro=0, gols_contra=0, saldo_gols=0, aproveitamento=0, ultimos_jogos=''");
  }

  static function salvarTabelaGerada($conn, $rounds) {
    self::limparEZerar($conn);

    $data_padrao = date('Y-m-d H:i:s', strtotime('first day of January 2026 20:00:00'));
    $stmt = $conn->prepare("INSERT INTO historico_jogos (time_casa, time_visitante, rodada, data_jogo) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
      error_log("Erro ao preparar INSERT: " . $conn->error);
      return false;
    }
    foreach ($rounds as $r => $matches) {
      $rodada = $r + 1;
      foreach ($matches as $m) {
        $stmt->bind_param("ssis", $m['home'], $m['away'], $rodada, $data_padrao);
        if (!$stmt->execute()) {
          error_log("Erro ao inserir jogo: " . $stmt->error);
        }
      }
    }
    $stmt->close();
    return true;
  }
}

class ConfigModel {
  static function get($conn, $chave) {
    $stmt = $conn->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
    $stmt->bind_param("s", $chave);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['valor'] ?? '';
  }

  static function set($conn, $chave, $valor) {
    $stmt = $conn->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt->bind_param("sss", $chave, $valor, $valor);
    $stmt->execute();
    $stmt->close();
  }

  static function getAll($conn) {
    $result = $conn->query("SELECT chave, valor FROM configuracoes");
    $dados = [];
    while ($row = $result->fetch_assoc()) {
      $dados[$row['chave']] = $row['valor'];
    }
    return $dados;
  }
}
