<?php
session_start();

require_once 'config.php';
require_once 'models.php';
require_once 'views.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
  error_log("DB connection failed: " . $conn->connect_error);
  die("Erro de conexão com o banco de dados.");
}

// --- CSRF ---
function gerarTokenCsrf() {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function verificarCsrf() {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
  if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
      !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    error_log("CSRF validation failed");
    header("Location: admin/login.php?error=csrf");
    exit();
  }
}

// --- Admin auth ---
function requerAdmin() {
  if (empty($_SESSION['admin_logged_in'])) {
    header("Location: admin/login.php");
    exit();
  }
}

// --- Ações POST ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {

  // --- Login ---
  if ($_GET['action'] === 'admin_login') {
    if (!isset($_POST['senha']) || !password_verify($_POST['senha'], ADMIN_PASSWORD_HASH)) {
      header("Location: admin/login.php?error=1");
      exit();
    }
    session_regenerate_id(true);
    $_SESSION['admin_logged_in'] = true;
    header("Location: admin/index.php");
    exit();
  }

  // --- Logout ---
  if ($_GET['action'] === 'admin_logout') {
    $_SESSION = [];
    session_destroy();
    header("Location: admin/login.php");
    exit();
  }

  // --- Todas as ações abaixo requerem admin logado ---
  verificarCsrf();
  requerAdmin();

  if ($_GET['action'] === 'save') {
    $jogo_id = (int)$_POST['jogo_id'];
    $gols_casa = (int)$_POST['gols_casa'];
    $gols_visitante = (int)$_POST['gols_visitante'];

    $jogo = JogoModel::buscarParaResultado($conn, $jogo_id);
    if (!$jogo) {
      header("Location: admin/jogos.php?error=3");
      exit();
    }

    if (!JogoModel::salvarResultado($conn, $jogo_id, $gols_casa, $gols_visitante)) {
      error_log("Erro ao salvar resultado do jogo $jogo_id");
      header("Location: admin/jogos.php?error=7");
      exit();
    }

    if ($gols_casa > $gols_visitante) {
      TimeModel::atualizarEstatisticas($conn, $jogo['time_casa'], $gols_casa, $gols_visitante, 'v', $jogo['rodada']);
      TimeModel::atualizarEstatisticas($conn, $jogo['time_visitante'], $gols_visitante, $gols_casa, 'd', $jogo['rodada']);
    } elseif ($gols_casa == $gols_visitante) {
      TimeModel::atualizarEstatisticas($conn, $jogo['time_casa'], $gols_casa, $gols_visitante, 'e', $jogo['rodada']);
      TimeModel::atualizarEstatisticas($conn, $jogo['time_visitante'], $gols_visitante, $gols_casa, 'e', $jogo['rodada']);
    } else {
      TimeModel::atualizarEstatisticas($conn, $jogo['time_casa'], $gols_casa, $gols_visitante, 'd', $jogo['rodada']);
      TimeModel::atualizarEstatisticas($conn, $jogo['time_visitante'], $gols_visitante, $gols_casa, 'v', $jogo['rodada']);
    }

    TimeModel::recalcularAproveitamento($conn);
    header("Location: admin/jogos.php?success=1");
    exit();
  }

  if ($_GET['action'] === 'save_next') {
    if (!isset($_POST['time_casa'], $_POST['time_visitante'], $_POST['rodada'], $_POST['data_jogo'])) {
      header("Location: admin/jogos.php?error=5");
      exit();
    }

    $time_casa = $_POST['time_casa'];
    $time_visitante = $_POST['time_visitante'];
    $rodada = (int)$_POST['rodada'];
    $data_jogo = $_POST['data_jogo'];

    if ($time_casa === $time_visitante) {
      header("Location: admin/jogos.php?error=1");
      exit();
    }

    if ($rodada < 1) {
      header("Location: admin/jogos.php?error=6");
      exit();
    }

    if (!strtotime($data_jogo)) {
      header("Location: admin/jogos.php?error=4");
      exit();
    }

    $data_formatada = date('Y-m-d H:i:s', strtotime($data_jogo));
    $resultado = JogoModel::cadastrar($conn, $time_casa, $time_visitante, $rodada, $data_formatada);

    if ($resultado === true) {
      header("Location: admin/jogos.php?success=2");
    } else {
      error_log("Erro ao cadastrar jogo: $resultado");
      header("Location: admin/jogos.php?error=7");
    }
    exit();
  }

  if ($_GET['action'] === 'edit') {
    $jogo_id = (int)$_POST['jogo_id'];
    $gols_casa = (int)$_POST['gols_casa'];
    $gols_visitante = (int)$_POST['gols_visitante'];

    $jogo = JogoModel::buscarCompleto($conn, $jogo_id);
    if (!$jogo || $jogo['gols_casa'] === null) {
      header("Location: admin/jogos.php?error=3");
      exit();
    }

    $gols_old_casa = (int)$jogo['gols_casa'];
    $gols_old_visitante = (int)$jogo['gols_visitante'];

    $res_casa = $gols_old_casa > $gols_old_visitante ? 'v' : ($gols_old_casa == $gols_old_visitante ? 'e' : 'd');
    $res_visitante = $gols_old_casa > $gols_old_visitante ? 'd' : ($gols_old_casa == $gols_old_visitante ? 'e' : 'v');

    TimeModel::reverterEstatisticas($conn, $jogo['time_casa'], $gols_old_casa, $gols_old_visitante, $res_casa);
    TimeModel::reverterEstatisticas($conn, $jogo['time_visitante'], $gols_old_visitante, $gols_old_casa, $res_visitante);

    JogoModel::limparResultado($conn, $jogo_id);
    TimeModel::reconstruirUltimosJogos($conn, $jogo['time_casa']);
    TimeModel::reconstruirUltimosJogos($conn, $jogo['time_visitante']);

    JogoModel::salvarResultado($conn, $jogo_id, $gols_casa, $gols_visitante);

    if ($gols_casa > $gols_visitante) {
      TimeModel::atualizarEstatisticas($conn, $jogo['time_casa'], $gols_casa, $gols_visitante, 'v', $jogo['rodada']);
      TimeModel::atualizarEstatisticas($conn, $jogo['time_visitante'], $gols_visitante, $gols_casa, 'd', $jogo['rodada']);
    } elseif ($gols_casa == $gols_visitante) {
      TimeModel::atualizarEstatisticas($conn, $jogo['time_casa'], $gols_casa, $gols_visitante, 'e', $jogo['rodada']);
      TimeModel::atualizarEstatisticas($conn, $jogo['time_visitante'], $gols_visitante, $gols_casa, 'e', $jogo['rodada']);
    } else {
      TimeModel::atualizarEstatisticas($conn, $jogo['time_casa'], $gols_casa, $gols_visitante, 'd', $jogo['rodada']);
      TimeModel::atualizarEstatisticas($conn, $jogo['time_visitante'], $gols_visitante, $gols_casa, 'v', $jogo['rodada']);
    }

    TimeModel::recalcularAproveitamento($conn);
    header("Location: admin/jogos.php?success=2");
    exit();
  }

  if ($_GET['action'] === 'add_team') {
    $nome = trim($_POST['nome'] ?? '');
    if ($nome === '') {
      header("Location: admin/times.php?error=8");
      exit();
    }

    $resultado = TimeModel::adicionar($conn, $nome);
    if ($resultado === true) {
      header("Location: admin/times.php?success=3");
    } else {
      error_log("Erro ao adicionar time: $resultado");
      header("Location: admin/times.php?error=7");
    }
    exit();
  }

  if ($_GET['action'] === 'delete_team') {
    $nome = $_POST['nome'] ?? '';
    if ($nome === '') {
      header("Location: admin/times.php?error=5");
      exit();
    }

    if (TimeModel::existeEmJogo($conn, $nome)) {
      header("Location: admin/times.php?error=9");
      exit();
    }

    if (TimeModel::remover($conn, $nome)) {
      header("Location: admin/times.php?success=4");
    } else {
      header("Location: admin/times.php?error=7");
    }
    exit();
  }

  if ($_GET['action'] === 'save_config') {
    if (!isset($_POST['campeonato_nome'], $_POST['campeonato_descricao'])) {
      header("Location: admin/configuracoes.php?error=5");
      exit();
    }
    ConfigModel::set($conn, 'campeonato_nome', $_POST['campeonato_nome']);
    ConfigModel::set($conn, 'campeonato_descricao', $_POST['campeonato_descricao']);
    header("Location: admin/configuracoes.php?success=1");
    exit();
  }
}

// --- Export CSV (GET) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'export_csv') {
  requerAdmin();
  $times = TimeModel::listar($conn, 'pontos', 'DESC', '');
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=tabela.csv');
  $output = fopen('php://output', 'w');
  fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
  fputcsv($output, ['#', 'Time', 'P', 'J', 'V', 'E', 'D', 'GP', 'GC', 'SG', '%']);
  $pos = 1;
  foreach ($times as $row) {
    fputcsv($output, [$pos, $row['nome'], $row['pontos'], $row['jogos'], $row['vitorias'], $row['empates'], $row['derrotas'], $row['gols_pro'], $row['gols_contra'], $row['saldo_gols'], $row['aproveitamento']]);
    $pos++;
  }
  fclose($output);
  exit();
}
