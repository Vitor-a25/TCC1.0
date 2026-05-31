<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin('admin');

$db = getDB();

function gerarBackupSQL(PDO $db): string {
    $sql  = "-- OPUS | Backup gerado em " . date('d/m/Y H:i:s') . "\n";
    $sql .= "-- ============================================================\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    $tabelas = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tabelas as $tabela) {
        $sql .= "-- Tabela: $tabela\n";
        $sql .= "DROP TABLE IF EXISTS `$tabela`;\n";

        $create = $db->query("SHOW CREATE TABLE `$tabela`")->fetch();
        $sql .= $create['Create Table'] . ";\n\n";

        $rows = $db->query("SELECT * FROM `$tabela`")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            $sql .= "INSERT INTO `$tabela` VALUES\n";
            $values = [];
            foreach ($rows as $row) {
                $escaped = array_map(function($v) use ($db) {
                    return is_null($v) ? 'NULL' : $db->quote($v);
                }, array_values($row));
                $values[] = '(' . implode(', ', $escaped) . ')';
            }
            $sql .= implode(",\n", $values) . ";\n\n";
        }
    }

    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return $sql;
}

$acao = $_GET['acao'] ?? '';

if ($acao === 'download') {
    $conteudo = gerarBackupSQL($db);
    $arquivo  = 'opus_backup_' . date('Ymd_His') . '.sql';
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $arquivo . '"');
    header('Content-Length: ' . strlen($conteudo));
    echo $conteudo;
    exit;
}

if ($acao === 'salvar') {
    $dir = __DIR__ . '/backups/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $conteudo = gerarBackupSQL($db);
    $arquivo  = $dir . 'opus_backup_' . date('Ymd_His') . '.sql';
    file_put_contents($arquivo, $conteudo);

    $db->prepare("INSERT INTO backup_log (arquivo, tamanho) VALUES (?, ?)")
       ->execute([basename($arquivo), strlen($conteudo)]);

    header('Location: backup.php?ok=1');
    exit;
}

if ($_GET['del'] ?? null) {
    $arquivo = __DIR__ . '/backups/' . basename($_GET['del']);
    if (file_exists($arquivo)) unlink($arquivo);
    header('Location: backup.php?del_ok=1');
    exit;
}

$db->exec("CREATE TABLE IF NOT EXISTS backup_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    arquivo VARCHAR(255),
    tamanho INT,
    criado_em DATETIME DEFAULT NOW()
)");

$logs = $db->query("SELECT * FROM backup_log ORDER BY criado_em DESC")->fetchAll();

$arquivos = [];
$dir = __DIR__ . '/backups/';
if (is_dir($dir)) {
    foreach (glob($dir . '*.sql') as $f) {
        $arquivos[] = [
            'nome'    => basename($f),
            'tamanho' => round(filesize($f) / 1024, 1),
            'data'    => date('d/m/Y H:i', filemtime($f)),
        ];
    }
    usort($arquivos, fn($a, $b) => strcmp($b['nome'], $a['nome']));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Backup | OPUS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<button class="menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>

<aside class="sidebar">
  <a href="index.php" class="sidebar-brand" style="text-decoration:none;color:#fff;">OPUS</a>
  <div class="sidebar-user">
    <strong><?= e($_SESSION['nome']) ?></strong>
    <span>Administrador do sistema</span>
    <div class="sidebar-badge admin">Admin</div>
  </div>
  <nav class="sidebar-nav">
    <a href="admin.php"><span class="icon">📊</span> Painel Geral</a>
    <a href="admin.php?aba=usuarios"><span class="icon">👥</span> Usuários</a>
    <a href="admin.php?aba=empresas"><span class="icon">🏢</span> Empresas</a>
    <a href="admin.php?aba=categorias"><span class="icon">🗂️</span> Categorias</a>
    <a href="admin.php?aba=avaliacoes"><span class="icon">⭐</span> Avaliações</a>
    <a href="backup.php" class="active"><span class="icon">💾</span> Backup</a>
  </nav>
  <div class="sidebar-footer">
    <a href="index.php">🌐 Ver site</a>
    <a href="logout.php">🚪 Sair</a>
  </div>
</aside>

<main class="main">

  <?php if ($_GET['ok'] ?? null): ?>
    <div class="alert alert-success">✅ Backup salvo com sucesso na pasta <code>backups/</code>!</div>
  <?php endif; ?>
  <?php if ($_GET['del_ok'] ?? null): ?>
    <div class="alert alert-success">🗑️ Arquivo de backup removido.</div>
  <?php endif; ?>

  <div class="topbar"><div><h1>💾 Backup do Banco de Dados</h1><p>Gere e gerencie backups completos da plataforma</p></div></div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px">

    <div class="card">
      <div class="card-header"><h2>⬇️ Download direto</h2></div>
      <p style="color:var(--muted);font-size:14px;margin-bottom:20px">
        Gera o backup completo do banco de dados e faz o download imediato do arquivo <code>.sql</code> no seu computador.
      </p>
      <a href="backup.php?acao=download" class="btn btn-primary" style="display:inline-flex">
        ⬇️ Baixar backup agora
      </a>
    </div>

    <div class="card">
      <div class="card-header"><h2>💾 Salvar no servidor</h2></div>
      <p style="color:var(--muted);font-size:14px;margin-bottom:20px">
        Salva o backup na pasta <code>backups/</code> dentro do projeto. Útil para manter um histórico de versões no servidor.
      </p>
      <a href="backup.php?acao=salvar" class="btn btn-success" style="display:inline-flex"
         onclick="return confirm('Salvar backup no servidor agora?')">
        💾 Salvar no servidor
      </a>
    </div>

  </div>

  <div class="card">
    <div class="card-header">
      <h2>📁 Backups salvos no servidor</h2>
      <span style="color:var(--muted);font-size:13px"><?= count($arquivos) ?> arquivo(s)</span>
    </div>
    <?php if ($arquivos): ?>
    <table>
      <thead><tr><th>Arquivo</th><th>Tamanho</th><th>Data</th><th>Ações</th></tr></thead>
      <tbody>
      <?php foreach ($arquivos as $f): ?>
        <tr>
          <td><code style="color:#d8b4fe"><?= e($f['nome']) ?></code></td>
          <td><?= $f['tamanho'] ?> KB</td>
          <td><?= $f['data'] ?></td>
          <td>
            <div class="btn-group">
              <a href="backups/<?= urlencode($f['nome']) ?>" download class="btn btn-outline btn-sm">⬇️ Baixar</a>
              <a href="backup.php?del=<?= urlencode($f['nome']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Excluir este backup?')">🗑️</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="empty-state"><div class="icon">📭</div><p>Nenhum backup salvo no servidor ainda.</p></div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-header"><h2>📋 Histórico de backups</h2></div>
    <?php if ($logs): ?>
    <table>
      <thead><tr><th>Arquivo</th><th>Tamanho</th><th>Data/Hora</th></tr></thead>
      <tbody>
      <?php foreach ($logs as $log): ?>
        <tr>
          <td><code style="color:#d8b4fe"><?= e($log['arquivo']) ?></code></td>
          <td><?= round($log['tamanho'] / 1024, 1) ?> KB</td>
          <td><?= date('d/m/Y H:i:s', strtotime($log['criado_em'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="empty-state"><div class="icon">📋</div><p>Nenhum backup registrado no histórico ainda.</p></div>
    <?php endif; ?>
  </div>

</main>
</body>
</html>
