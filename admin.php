<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin('admin');

$db   = getDB();
$nome = $_SESSION['nome'];
$msg  = '';


if ($_GET['del_user'] ?? null) {
    $uid = (int)$_GET['del_user'];
    $db->prepare('DELETE FROM usuario WHERE id=? AND tipo != "admin"')->execute([$uid]);
    redirect('admin.php?aba=usuarios&ok=Usuario+removido');
}


if ($_GET['toggle_emp'] ?? null) {
    $eid = (int)$_GET['toggle_emp'];
    $db->prepare('UPDATE empresa SET ativo = NOT ativo WHERE id=?')->execute([$eid]);
    redirect('admin.php?aba=empresas&ok=Status+alterado');
}

if ($_GET['del_emp'] ?? null) {
    $db->prepare('DELETE FROM empresa WHERE id=?')->execute([(int)$_GET['del_emp']]);
    redirect('admin.php?aba=empresas&ok=Empresa+removida');
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar_cat') {
    $cn  = trim($_POST['cat_nome']  ?? '');
    $ci  = trim($_POST['cat_icone'] ?? '') ?: '';
    $cid = (int)($_POST['cat_id']   ?? 0);
    if ($cn) {
        if ($cid) {
            $db->prepare('UPDATE categoria SET nome=?,icone=? WHERE id=?')->execute([$cn,$ci,$cid]);
            redirect('admin.php?aba=categorias&ok=Categoria+atualizada+com+sucesso');
        } else {
            $db->prepare('INSERT INTO categoria (nome,icone) VALUES (?,?)')->execute([$cn,$ci]);
            redirect('admin.php?aba=categorias&ok=Categoria+criada+com+sucesso');
        }
    } else { $msg = 'error:Nome da categoria é obrigatório.'; }
}

if ($_GET['del_cat'] ?? null) {
    $db->prepare('DELETE FROM categoria WHERE id=?')->execute([(int)$_GET['del_cat']]);
    redirect('admin.php?aba=categorias&ok=Categoria+removida');
}

if ($_GET['del_av'] ?? null) {
    $db->prepare('DELETE FROM avaliacao WHERE id=?')->execute([(int)$_GET['del_av']]);
    redirect('admin.php?aba=avaliacoes&ok=Avaliacao+removida');
}
if ($_GET['moderar_av'] ?? null) {
    $db->prepare('UPDATE avaliacao SET moderado = NOT moderado WHERE id=?')->execute([(int)$_GET['moderar_av']]);
    redirect('admin.php?aba=avaliacoes&ok=Status+de+moderacao+alterado');
}

$aba = $_GET['aba'] ?? 'painel';
if (isset($_GET['ok'])) $msg = 'success:' . urldecode($_GET['ok']);

$totalUsers = $db->query("SELECT COUNT(*) FROM usuario WHERE tipo='cliente'")->fetchColumn();
$totalEmps  = $db->query("SELECT COUNT(*) FROM empresa")->fetchColumn();
$totalSols  = $db->query("SELECT COUNT(*) FROM solicitacao")->fetchColumn();
$totalAv    = $db->query("SELECT COUNT(*) FROM avaliacao")->fetchColumn();

$usuarios   = $db->query("SELECT * FROM usuario ORDER BY criado_em DESC")->fetchAll();
$empresas   = $db->query("SELECT e.*, u.nome as u_nome, AVG(a.nota) as media, COUNT(DISTINCT a.id) as total_av FROM empresa e LEFT JOIN usuario u ON u.id=e.usuario_id LEFT JOIN avaliacao a ON a.empresa_id=e.id GROUP BY e.id ORDER BY e.criado_em DESC")->fetchAll();
$categorias = $db->query("SELECT c.*, COUNT(s.id) as total_srv FROM categoria c LEFT JOIN servico s ON s.categoria_id=c.id GROUP BY c.id ORDER BY c.nome")->fetchAll();
$avaliacoes = $db->query("SELECT a.*, u.nome as u_nome, e.nome as e_nome FROM avaliacao a JOIN usuario u ON u.id=a.usuario_id JOIN empresa e ON e.id=a.empresa_id ORDER BY a.data DESC")->fetchAll();

$editCat = null;
if (isset($_GET['edit_cat'])) {
    $ec = $db->prepare('SELECT * FROM categoria WHERE id=?');
    $ec->execute([(int)$_GET['edit_cat']]);
    $editCat = $ec->fetch();
}

[$msgTipo, $msgText] = $msg ? explode(':', $msg, 2) : ['', ''];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Painel Admin | OPUS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<button class="menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>

<aside class="sidebar">
  <a href="index.php" class="sidebar-brand" style="text-decoration:none;color:#fff;">OPUS</a>
  <div class="sidebar-user">
    <strong><?= e($nome) ?></strong>
    <span>Administrador do sistema</span>
    <div class="sidebar-badge admin">Admin</div>
  </div>
  <nav class="sidebar-nav">
    <a href="?aba=painel"     class="<?= $aba==='painel'?'active':'' ?>"><span class="icon">📊</span> Painel Geral</a>
    <a href="?aba=usuarios"   class="<?= $aba==='usuarios'?'active':'' ?>"><span class="icon">👥</span> Usuários</a>
    <a href="?aba=empresas"   class="<?= $aba==='empresas'?'active':'' ?>"><span class="icon">🏢</span> Empresas</a>
    <a href="?aba=categorias" class="<?= $aba==='categorias'?'active':'' ?>"><span class="icon">🗂️</span> Categorias</a>
    <a href="?aba=avaliacoes" class="<?= $aba==='avaliacoes'?'active':'' ?>"><span class="icon">⭐</span> Avaliações</a>
    <a href="backup.php"><span class="icon">💾</span> Backup</a>
  </nav>
  <div class="sidebar-footer">
    <a href="index.php">🌐 Ver site</a>
    <a href="logout.php">🚪 Sair</a>
  </div>
</aside>

<main class="main">

  <?php if ($msgText): ?>
    <div class="alert alert-<?= $msgTipo === 'success' ? 'success' : 'error' ?>"><?= e($msgText) ?></div>
  <?php endif; ?>

  <?php if ($aba === 'painel'): ?>
  <div class="topbar"><div><h1>📊 Painel Administrativo</h1><p>Visão geral da plataforma</p></div></div>
  <div class="stats-row">
    <div class="stat-card"><div class="label">Clientes</div><div class="value"><?= $totalUsers ?></div><div class="sub">cadastrados</div></div>
    <div class="stat-card"><div class="label">Empresas</div><div class="value" style="color:var(--blue)"><?= $totalEmps ?></div><div class="sub">ativas</div></div>
    <div class="stat-card"><div class="label">Solicitações</div><div class="value" style="color:var(--yellow)"><?= $totalSols ?></div><div class="sub">total</div></div>
    <div class="stat-card"><div class="label">Avaliações</div><div class="value" style="color:#facc15"><?= $totalAv ?></div><div class="sub">registradas</div></div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px">
    <div class="card">
      <div class="card-header"><h2>👥 Usuários</h2></div>
      <p style="color:var(--muted);font-size:14px">Gerencie clientes cadastrados, visualize atividade e remova contas quando necessário.</p>
      <div style="margin-top:16px"><a href="?aba=usuarios" class="btn btn-primary btn-sm">Acessar</a></div>
    </div>
    <div class="card">
      <div class="card-header"><h2>🏢 Empresas</h2></div>
      <p style="color:var(--muted);font-size:14px">Ative, suspenda ou remova empresas prestadoras de serviço da plataforma.</p>
      <div style="margin-top:16px"><a href="?aba=empresas" class="btn btn-primary btn-sm">Acessar</a></div>
    </div>
    <div class="card">
      <div class="card-header"><h2>🗂️ Categorias</h2></div>
      <p style="color:var(--muted);font-size:14px">Organize o catálogo criando e editando as categorias de serviços disponíveis.</p>
      <div style="margin-top:16px"><a href="?aba=categorias" class="btn btn-primary btn-sm">Acessar</a></div>
    </div>
  </div>

  <?php elseif ($aba === 'usuarios'): ?>
  <div class="topbar"><div><h1>👥 Usuários</h1><p>Monitore e gerencie as contas da plataforma</p></div></div>
  <div class="card">
    <div class="search-bar" style="margin-bottom:20px">
      <input type="text" id="filtroUsuario" placeholder="Buscar por nome ou e-mail..." oninput="filtrarUsuarios()">
      <select id="filtroTipo" onchange="filtrarUsuarios()" style="flex:0 0 160px">
        <option value="">Todos os perfis</option>
        <option value="cliente">Cliente</option>
        <option value="empresa">Empresa</option>
        <option value="admin">Admin</option>
      </select>
    </div>
    <table id="tabelaUsuarios">
      <thead><tr><th>#</th><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Telefone</th><th>Cadastro</th><th>Ações</th></tr></thead>
      <tbody>
      <?php foreach ($usuarios as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><strong><?= e($u['nome']) ?></strong></td>
          <td><?= e($u['email']) ?></td>
          <td>
            <?php $bc = ['cliente'=>'badge-active','empresa'=>'badge-progress','admin'=>'badge-cancelled']; ?>
            <span class="badge <?= $bc[$u['tipo']] ?? '' ?>"><?= e($u['tipo']) ?></span>
          </td>
          <td><?= e($u['telefone'] ?? '—') ?></td>
          <td><?= date('d/m/Y',strtotime($u['criado_em'])) ?></td>
          <td>
            <?php if ($u['tipo'] !== 'admin'): ?>
              <a href="?del_user=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remover este usuário?')">🗑️ Remover</a>
            <?php else: ?>
              <span style="color:var(--muted);font-size:12px">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php elseif ($aba === 'empresas'): ?>
  <div class="topbar"><div><h1>🏢 Empresas</h1><p>Gerencie os perfis das empresas parceiras</p></div></div>
  <div class="card">
    <table>
      <thead><tr><th>#</th><th>Empresa</th><th>CNPJ</th><th>Cidade</th><th>Avaliação</th><th>Status</th><th>Ações</th></tr></thead>
      <tbody>
      <?php foreach ($empresas as $emp): ?>
        <tr>
          <td><?= $emp['id'] ?></td>
          <td>
            <strong><?= e($emp['nome']) ?></strong><br>
            <small style="color:var(--muted)">Resp: <?= e($emp['u_nome'] ?? '—') ?></small>
          </td>
          <td><?= e($emp['cnpj'] ?? '—') ?></td>
          <td><?= e($emp['cidade'].', '.$emp['estado']) ?></td>
          <td><span class="stars"><?= $emp['media'] ? number_format($emp['media'],1).' ★' : '—' ?></span> <small style="color:var(--muted)">(<?= $emp['total_av'] ?>)</small></td>
          <td><span class="badge <?= $emp['ativo'] ? 'badge-active' : 'badge-inactive' ?>"><?= $emp['ativo'] ? 'Ativa' : 'Suspensa' ?></span></td>
          <td>
            <div class="btn-group">
              <a href="?aba=empresas&toggle_emp=<?= $emp['id'] ?>" class="btn btn-outline btn-sm"><?= $emp['ativo'] ? '🔒 Suspender' : '✅ Ativar' ?></a>
              <a href="?aba=empresas&del_emp=<?= $emp['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remover empresa permanentemente?')">🗑️</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php elseif ($aba === 'categorias'): ?>
  <div class="topbar"><div><h1>🗂️ Categorias</h1><p>Organize as categorias de serviços da plataforma</p></div></div>
  <div class="card" style="max-width:500px">
    <div class="card-header"><h2><?= $editCat ? 'Editar Categoria' : 'Nova Categoria' ?></h2></div>
    <form method="POST">
      <input type="hidden" name="acao" value="salvar_cat">
      <?php if ($editCat): ?><input type="hidden" name="cat_id" value="<?= $editCat['id'] ?>"><?php endif; ?>
      <div class="form-grid two">
        <div class="form-group"><label>Nome *</label><input type="text" name="cat_nome" value="<?= e($editCat['nome'] ?? '') ?>" required placeholder="Ex: Manutenção"></div>
        <div class="form-group"><label>Ícone (emoji)</label><input type="text" name="cat_icone" value="<?= e($editCat['icone'] ?? '') ?>" maxlength="4" placeholder="" style="background:rgba(255,255,255,.07);color:#fff;border:1px solid rgba(255,255,255,.13);"></div>
      </div>
      <div style="margin-top:14px;display:flex;gap:10px">
        <button type="submit" class="btn btn-primary"><?= $editCat ? 'Salvar edição' : 'Criar categoria' ?></button>
        <?php if ($editCat): ?><a href="?aba=categorias" class="btn btn-outline">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>
  <div class="card">
    <table>
      <thead><tr><th>Ícone</th><th>Nome</th><th>Serviços</th><th>Ações</th></tr></thead>
      <tbody>
      <?php foreach ($categorias as $c): ?>
        <tr>
          <td style="font-size:22px"><?= e($c['icone']) ?></td>
          <td><strong><?= e($c['nome']) ?></strong></td>
          <td><?= $c['total_srv'] ?></td>
          <td>
            <div class="btn-group">
              <a href="?aba=categorias&edit_cat=<?= $c['id'] ?>" class="btn btn-outline btn-sm">✏️ Editar</a>
              <a href="?aba=categorias&del_cat=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Excluir esta categoria?')">🗑️</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php elseif ($aba === 'avaliacoes'): ?>
  <div class="topbar"><div><h1>⭐ Avaliações</h1><p>Modere as avaliações dos usuários</p></div></div>
  <div class="card">
    <table>
      <thead><tr><th>#</th><th>Cliente</th><th>Empresa</th><th>Nota</th><th>Comentário</th><th>Data</th><th>Status</th><th>Ações</th></tr></thead>
      <tbody>
      <?php foreach ($avaliacoes as $av): ?>
        <tr>
          <td><?= $av['id'] ?></td>
          <td><?= e($av['u_nome']) ?></td>
          <td><?= e($av['e_nome']) ?></td>
          <td><span class="stars"><?= formatStars($av['nota']) ?></span></td>
          <td style="max-width:180px;color:var(--muted)"><?= e(mb_strimwidth($av['comentario'],0,80,'...')) ?: '<em>Sem comentário</em>' ?></td>
          <td><?= date('d/m/Y',strtotime($av['data'])) ?></td>
          <td><span class="badge <?= $av['moderado'] ? 'badge-inactive' : 'badge-active' ?>"><?= $av['moderado'] ? 'Moderado' : 'Visível' ?></span></td>
          <td>
            <div class="btn-group">
              <a href="?aba=avaliacoes&moderar_av=<?= $av['id'] ?>" class="btn btn-outline btn-sm"><?= $av['moderado'] ? '👁️ Reativar' : '🚫 Moderar' ?></a>
              <a href="?aba=avaliacoes&del_av=<?= $av['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Excluir avaliação?')">🗑️</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$avaliacoes): ?>
        <tr><td colspan="8"><div class="empty-state"><div class="icon">⭐</div><p>Nenhuma avaliação registrada.</p></div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php endif; ?>

</main>

<script>
function filtrarUsuarios() {
  const termo  = document.getElementById('filtroUsuario').value.toLowerCase();
  const tipo   = document.getElementById('filtroTipo').value.toLowerCase();
  const linhas = document.querySelectorAll('#tabelaUsuarios tbody tr');
  linhas.forEach(tr => {
    const texto  = tr.innerText.toLowerCase();
    const perfil = tr.querySelector('.badge')?.innerText.toLowerCase() ?? '';
    tr.style.display = texto.includes(termo) && (!tipo || perfil === tipo) ? '' : 'none';
  });
}
</script>
</body>
</html>