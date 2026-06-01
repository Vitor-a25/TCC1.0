<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin('admin');

$db = getDB();

$db->exec("CREATE TABLE IF NOT EXISTS backup_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    arquivo VARCHAR(255),
    tamanho INT,
    criado_em DATETIME DEFAULT NOW()
)");

$logs = $db->query("SELECT * FROM backup_log ORDER BY criado_em DESC")->fetchAll();
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

  <div class="topbar">
    <div>
      <h1>💾 Backup do Banco de Dados</h1>
      <p>Backups automáticos diários — mantidos por 7 dias</p>
    </div>
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