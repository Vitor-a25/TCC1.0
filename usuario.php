<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();

$db   = getDB();
$uid  = $_SESSION['usuario_id'];
$nome = $_SESSION['nome'];
$msg  = '';

// CONFIRMAR SERVICO via GET
if (isset($_GET['confirmar']) && ctype_digit($_GET['confirmar'])) {
    $sol_id = (int)$_GET['confirmar'];
    $upd = $db->prepare("UPDATE solicitacao SET status = 'Servico Realizado', data_conclusao = ? WHERE id = ? AND usuario_id = ?");
    $upd->execute([date('Y-m-d'), $sol_id, $uid]);
    redirect('usuario.php?aba=solicitacoes&ok=Servico+confirmado+com+sucesso!');
}

// SOLICITAR SERVICO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'solicitar') {
    $emp_id_sol = (int)($_POST['empresa_id'] ?? 0);
    $desc   = trim($_POST['descricao']   ?? '');
    $prior  = $_POST['prioridade']        ?? 'Media';

    // Bloqueia empresa de solicitar seu proprio servico
    $propria = false;
    if (getTipo() === 'empresa') {
        $chk = $db->prepare('SELECT id FROM empresa WHERE usuario_id = ? AND id = ?');
        $chk->execute([$uid, $emp_id_sol]);
        $propria = (bool)$chk->fetch();
    }

    if ($propria) {
        $msg = 'error:Voce nao pode solicitar seu proprio servico.';
    } elseif ($emp_id_sol && $desc) {
        $db->prepare('INSERT INTO solicitacao (usuario_id, empresa_id, descricao, prioridade) VALUES (?,?,?,?)')
           ->execute([$uid, $emp_id_sol, $desc, $prior]);
        redirect('usuario.php?aba=solicitacoes&ok=Solicitacao+enviada+com+sucesso');
    } else {
        $msg = 'error:Preencha todos os campos.';
    }
}

// AVALIAR EMPRESA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'avaliar') {
    $sol_id = (int)($_POST['solicitacao_id'] ?? 0);
    $emp_id = (int)($_POST['empresa_id']     ?? 0);
    $nota   = (int)($_POST['nota']           ?? 0);
    $coment = trim($_POST['comentario']      ?? '');
    if ($sol_id && $emp_id && $nota >= 1 && $nota <= 5) {
        $chk = $db->prepare('SELECT id FROM avaliacao WHERE solicitacao_id = ?');
        $chk->execute([$sol_id]);
        if (!$chk->fetch()) {
            $db->prepare('INSERT INTO avaliacao (usuario_id, empresa_id, solicitacao_id, nota, comentario) VALUES (?,?,?,?,?)')
               ->execute([$uid, $emp_id, $sol_id, $nota, $coment]);
            $msg = 'success:Avaliacao registrada com sucesso!';
        } else {
            $msg = 'error:Esta solicitacao ja foi avaliada.';
        }
    } else {
        $msg = 'error:Selecione uma nota para avaliar.';
    }
}

// SALVAR DADOS PESSOAIS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar_dados') {
    $n   = trim($_POST['nome']     ?? '');
    $tel = trim($_POST['telefone'] ?? '');
    $end = trim($_POST['endereco'] ?? '');
    $cid = trim($_POST['cidade']   ?? '');
    $est = trim($_POST['estado']   ?? '');
    if (!$n) {
        $msg = 'error:O nome e obrigatorio.';
    } else {
        $db->prepare('UPDATE usuario SET nome=?, telefone=?, endereco=?, cidade=?, estado=? WHERE id=?')
           ->execute([$n, $tel, $end, $cid, $est, $uid]);
        $_SESSION['nome'] = $n;
        $nome = $n;
        $msg = 'success:Dados atualizados com sucesso!';
    }
}

// TROCAR SENHA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'trocar_senha') {
    $atual   = $_POST['senha_atual']   ?? '';
    $nova    = $_POST['senha_nova']    ?? '';
    $confirm = $_POST['senha_confirm'] ?? '';
    $chk = $db->prepare('SELECT senha FROM usuario WHERE id = ?');
    $chk->execute([$uid]);
    $hashAtual = $chk->fetchColumn();
    if (!password_verify($atual, $hashAtual)) {
        $msg = 'error:Senha atual incorreta.';
    } elseif (strlen($nova) < 8) {
        $msg = 'error:A nova senha deve ter no minimo 8 caracteres.';
    } elseif (!preg_match('/[A-Z]/', $nova) || !preg_match('/[a-z]/', $nova) || !preg_match('/[0-9]/', $nova) || !preg_match('/[\W_]/', $nova)) {
        $msg = 'error:A nova senha deve conter maiuscula, minuscula, numero e caractere especial.';
    } elseif ($nova !== $confirm) {
        $msg = 'error:As senhas nao coincidem.';
    } else {
        $db->prepare('UPDATE usuario SET senha=? WHERE id=?')->execute([password_hash($nova, PASSWORD_DEFAULT), $uid]);
        $msg = 'success:Senha alterada com sucesso!';
    }
}

$aba   = $_GET['aba'] ?? 'buscar';
if (isset($_GET['ok'])) $msg = 'success:' . urldecode($_GET['ok']);
$termo = trim($_GET['q'] ?? '');

$sqlEmp = 'SELECT e.*, AVG(a.nota) as media, COUNT(DISTINCT a.id) as total_av
           FROM empresa e
           LEFT JOIN avaliacao a ON a.empresa_id = e.id
           LEFT JOIN servico s ON s.empresa_id = e.id
           LEFT JOIN categoria c ON c.id = s.categoria_id
           WHERE e.ativo = 1';
$params = [];
if ($termo) {
    $sqlEmp .= ' AND (e.nome LIKE ? OR e.descricao LIKE ?)';
    $params[] = "%$termo%";
    $params[] = "%$termo%";
}
if (!empty($_GET['cidade'])) {
    $sqlEmp .= ' AND e.cidade = ?';
    $params[] = $_GET['cidade'];
}
if (!empty($_GET['cat'])) {
    $sqlEmp .= ' AND c.id = ?';
    $params[] = (int)$_GET['cat'];
}
$sqlEmp .= ' GROUP BY e.id, e.nome, e.cnpj, e.email, e.telefone, e.endereco, e.cidade, e.estado, e.descricao, e.ativo, e.criado_em, e.usuario_id';
if (!empty($_GET['av'])) {
    $sqlEmp .= ' HAVING media >= ?';
    $params[] = (float)$_GET['av'];
}
$sqlEmp .= ' ORDER BY media DESC';
$stmtEmp = $db->prepare($sqlEmp);
$stmtEmp->execute($params);
$empresas = $stmtEmp->fetchAll();

$cats    = $db->query('SELECT * FROM categoria ORDER BY nome')->fetchAll();
$cidades = $db->query("SELECT DISTINCT cidade FROM empresa WHERE ativo=1 AND cidade != '' ORDER BY cidade")->fetchAll(PDO::FETCH_COLUMN);

$empSel = null;
if (isset($_GET['empresa'])) {
    $s = $db->prepare('SELECT e.*, AVG(a.nota) as media, COUNT(DISTINCT a.id) as total_av FROM empresa e LEFT JOIN avaliacao a ON a.empresa_id = e.id WHERE e.id = ? GROUP BY e.id');
    $s->execute([(int)$_GET['empresa']]);
    $empSel = $s->fetch();
    if ($empSel) {
        $srvs = $db->prepare('SELECT s.*, c.nome as cat_nome FROM servico s LEFT JOIN categoria c ON c.id = s.categoria_id WHERE s.empresa_id = ? AND s.ativo = 1');
        $srvs->execute([$empSel['id']]);
        $empSel['servicos'] = $srvs->fetchAll();
        $avs = $db->prepare('SELECT a.*, u.nome as u_nome FROM avaliacao a JOIN usuario u ON u.id = a.usuario_id WHERE a.empresa_id = ? AND a.moderado = 0 ORDER BY a.data DESC LIMIT 5');
        $avs->execute([$empSel['id']]);
        $empSel['avaliacoes'] = $avs->fetchAll();
    }
}

$sols = $db->prepare('SELECT s.*, e.nome as emp_nome, e.telefone as emp_tel,
                             f.nome as func_nome, f.telefone as func_tel, f.cargo as func_cargo
                      FROM solicitacao s
                      JOIN empresa e ON e.id = s.empresa_id
                      LEFT JOIN funcionario f ON f.id = s.funcionario_id
                      WHERE s.usuario_id = ? ORDER BY s.data_solicitacao DESC');
$sols->execute([$uid]);
$minhasSols = $sols->fetchAll();

$totalSols  = count($minhasSols);
$concluidas = count(array_filter($minhasSols, fn($s) => $s['status'] === 'Servico Realizado'));
$pendentes  = count(array_filter($minhasSols, fn($s) => $s['status'] === 'Pendente'));
$avalSt     = $db->prepare('SELECT COUNT(*) FROM avaliacao WHERE usuario_id = ?');
$avalSt->execute([$uid]);
$totalAv = $avalSt->fetchColumn();

$semAval = array_filter($minhasSols, function($s) use ($db) {
    if ($s['status'] !== 'Servico Realizado') return false;
    $c = $db->prepare('SELECT id FROM avaliacao WHERE solicitacao_id = ?');
    $c->execute([$s['id']]);
    return !$c->fetch();
});

$du = $db->prepare('SELECT * FROM usuario WHERE id = ?');
$du->execute([$uid]);
$dadosUser = $du->fetch();

[$msgTipo, $msgText] = $msg ? explode(':', $msg, 2) : ['', ''];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Painel do Usuario | OPUS</title>
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
    <span>Bem-vindo!</span>
    <div class="sidebar-badge <?= getTipo()==='empresa'?'empresa':'' ?>"><?= ucfirst(getTipo()) ?></div>
  </div>
  <nav class="sidebar-nav">
    <a href="?aba=buscar"       class="<?= $aba==='buscar'?'active':'' ?>"><span class="icon">🔍</span> Buscar Servicos</a>
    <a href="?aba=solicitacoes" class="<?= $aba==='solicitacoes'?'active':'' ?>"><span class="icon">📋</span> Minhas Solicitacoes</a>
    <a href="?aba=avaliacoes"   class="<?= $aba==='avaliacoes'?'active':'' ?>"><span class="icon">⭐</span> Avaliar Empresas</a>
    <?php if (getTipo() !== 'empresa'): ?>
    <a href="?aba=meusdados"    class="<?= $aba==='meusdados'?'active':'' ?>"><span class="icon">👤</span> Meus Dados</a>
    <?php endif; ?>
    <?php if (getTipo() === 'empresa'): ?>
    <a href="empresa.php"><span class="icon">🏢</span> Voltar ao Painel</a>
    <?php endif; ?>
  </nav>
  <div class="sidebar-footer">
    <a href="logout.php">🚪 Sair</a>
  </div>
</aside>

<main class="main">

  <?php if ($msgText): ?>
    <div class="alert alert-<?= $msgTipo === 'success' ? 'success' : 'error' ?>"><?= e($msgText) ?></div>
  <?php endif; ?>

  <div class="stats-row">
    <div class="stat-card"><div class="label">Total de Solicitacoes</div><div class="value"><?= $totalSols ?></div></div>
    <div class="stat-card"><div class="label">Pendentes</div><div class="value" style="color:var(--yellow)"><?= $pendentes ?></div></div>
    <div class="stat-card"><div class="label">Realizados</div><div class="value" style="color:var(--green)"><?= $concluidas ?></div></div>
    <div class="stat-card"><div class="label">Avaliacoes feitas</div><div class="value" style="color:var(--purple)"><?= $totalAv ?></div></div>
  </div>

  <?php if ($aba === 'buscar'): ?>
  <div class="topbar">
    <div><h1>🔍 Buscar Servicos</h1><p>Pesquise empresas por nome, cidade ou categoria</p></div>
  </div>
  <form method="GET" class="search-bar" style="flex-wrap:wrap;gap:10px">
    <input type="hidden" name="aba" value="buscar">
    <button type="submit" class="btn btn-primary">Buscar</button>
    <select name="cat" style="flex:0 0 180px">
      <option value="">Todas as categorias</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= $c['id'] ?>" <?= (($_GET['cat']??'')==$c['id'])?'selected':'' ?>><?= e($c['icone'].' '.$c['nome']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="cidade" style="flex:0 0 180px">
      <option value="">Todas as cidades</option>
      <?php foreach ($cidades as $c): ?>
        <option value="<?= e($c) ?>" <?= ($_GET['cidade']??'')===$c?'selected':'' ?>><?= e($c) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="av" style="flex:0 0 160px">
      <option value="">Todas as avaliacoes</option>
      <option value="5" <?= ($_GET['av']??'')=='5'?'selected':'' ?>>5 estrelas</option>
      <option value="4" <?= ($_GET['av']??'')=='4'?'selected':'' ?>>4+ estrelas</option>
      <option value="3" <?= ($_GET['av']??'')=='3'?'selected':'' ?>>3+ estrelas</option>
      <option value="2" <?= ($_GET['av']??'')=='2'?'selected':'' ?>>2+ estrelas</option>
      <option value="1" <?= ($_GET['av']??'')=='1'?'selected':'' ?>>1+ estrela</option>
    </select>
    <input type="text" name="q" placeholder="Ex: eletricista, frete, informatica..." value="<?= e($termo) ?>" style="flex:1;min-width:200px">
  </form>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px">
    <?php foreach ($empresas as $emp): ?>
    <div style="background:var(--card);border:1px solid var(--line);border-radius:22px;padding:24px;">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
        <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--purple),var(--purple2));display:grid;place-items:center;font-weight:800;font-size:16px;flex-shrink:0;">
          <?= mb_strtoupper(mb_substr($emp['nome'],0,2)) ?>
        </div>
        <div>
          <strong style="display:block"><?= e($emp['nome']) ?></strong>
          <small style="color:var(--muted)"><?= e($emp['cidade'] . ', ' . $emp['estado']) ?></small>
        </div>
      </div>
      <p style="color:var(--muted);font-size:13px;margin-bottom:14px;"><?= e(mb_strimwidth($emp['descricao'],0,100,'...')) ?></p>
      <div style="color:#facc15;font-size:14px;margin-bottom:14px;">
        <?= $emp['media'] ? formatStars((int)round($emp['media'])) : '☆☆☆☆☆' ?>
        <span style="color:var(--muted);font-size:12px"> <?= $emp['media'] ? number_format($emp['media'],1) : '—' ?> • <?= $emp['total_av'] ?> av.</span>
      </div>
      <a href="?aba=buscar&empresa=<?= $emp['id'] ?>" class="btn btn-primary" style="width:100%;justify-content:center;">Ver empresa & Solicitar</a>
    </div>
    <?php endforeach; ?>
    <?php if (!$empresas): ?>
      <div class="empty-state" style="grid-column:1/-1"><div class="icon">🔎</div><p>Nenhuma empresa encontrada.</p></div>
    <?php endif; ?>
  </div>

  <?php elseif ($aba === 'solicitacoes'): ?>
  <div class="topbar"><div><h1>📋 Minhas Solicitacoes</h1><p>Acompanhe o status dos seus pedidos</p></div></div>
  <div class="card">
    <?php if ($minhasSols): ?>
    <table>
      <thead><tr><th>Empresa</th><th>Descricao</th><th>Prioridade</th><th>Status</th><th>Data</th><th>Resposta</th></tr></thead>
      <tbody>
      <?php foreach ($minhasSols as $s): ?>
        <tr>
          <td>
            <?= e($s['emp_nome']) ?>
            <?php if ($s['emp_tel']): ?>
              <br><small style="color:var(--muted)"><?= e($s['emp_tel']) ?></small>
            <?php endif; ?>
          </td>
          <td style="max-width:200px"><?= e(mb_strimwidth($s['descricao'],0,80,'...')) ?></td>
          <td><?= e($s['prioridade']) ?></td>
          <td>
            <?php $sc = ['Pendente'=>'badge-pending','Em andamento'=>'badge-progress','Servico Realizado'=>'badge-done','Cancelado'=>'badge-cancelled']; ?>
            <span class="badge <?= $sc[$s['status']] ?? '' ?>"><?= e($s['status']) ?></span>
            <?php if (!in_array($s['status'], ['Servico Realizado','Cancelado','Pendente'])): ?>
              <a href="?aba=solicitacoes&confirmar=<?= $s['id'] ?>"
                 onclick="return confirm('Confirmar que o servico foi realizado?')"
                 style="display:inline-block;margin-top:8px;padding:6px 12px;border-radius:8px;background:linear-gradient(135deg,var(--purple),var(--purple2));color:#fff;font-size:12px;font-weight:700;text-decoration:none;">
                ✅ Confirmar servico
              </a>
            <?php endif; ?>
          </td>
          <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($s['data_solicitacao'])) ?></td>
          <td style="font-size:13px;max-width:220px">
            <?php if ($s['resposta']): ?>
              <span style="color:var(--muted)"><?= e(mb_strimwidth($s['resposta'],0,80,'...')) ?></span>
            <?php else: ?>
              <em style="color:var(--muted)">Aguardando...</em>
            <?php endif; ?>
            <?php if ($s['func_nome']): ?>
              <br><br>
              <small style="color:var(--muted);font-size:11px">👷 <?= e($s['func_nome']) ?><?= $s['func_tel'] ? ' - ' . e($s['func_tel']) : '' ?></small>
            <?php endif; ?>
            <?php if ($s['inicio_previsto'] || $s['tempo_deslocamento'] || $s['tempo_estimado']): ?>
              <div style="margin-top:10px;padding:10px 12px;background:rgba(168,85,247,.1);border:1px solid rgba(168,85,247,.25);border-radius:12px;display:grid;gap:5px;">
                <?php if ($s['inicio_previsto']): ?>
                  <span style="font-size:12px">🕐 <strong>Previsao de chegada:</strong> <?= e($s['inicio_previsto']) ?></span>
                <?php endif; ?>
                <?php if ($s['tempo_estimado']): ?>
                  <span style="font-size:12px">⏱️ <strong>Duracao estimada:</strong> <?= e($s['tempo_estimado']) ?></span>
                <?php endif; ?>
                <?php if ($s['fora_horario']): ?>
                  <span style="font-size:11px;color:#fbbf24;margin-top:2px">⚠️ Este servico pode nao ser concluido hoje devido ao horario.</span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="empty-state"><div class="icon">📭</div><p>Voce ainda nao fez nenhuma solicitacao.<br><a href="?aba=buscar" style="color:var(--purple)">Buscar empresas</a></p></div>
    <?php endif; ?>
  </div>

  <?php elseif ($aba === 'avaliacoes'): ?>
  <div class="topbar"><div><h1>⭐ Avaliar Empresas</h1><p>Avalie os servicos que voce contratou</p></div></div>
  <?php if ($semAval): ?>
  <div style="display:grid;gap:18px">
    <?php foreach ($semAval as $s): ?>
    <div class="card">
      <div class="card-header">
        <div>
          <h2><?= e($s['emp_nome']) ?></h2>
          <p style="color:var(--muted);font-size:13px"><?= e($s['descricao']) ?></p>
        </div>
      </div>
      <form method="POST">
        <input type="hidden" name="acao" value="avaliar">
        <input type="hidden" name="solicitacao_id" value="<?= $s['id'] ?>">
        <input type="hidden" name="empresa_id" value="<?= $s['empresa_id'] ?>">
        <div class="form-grid two">
          <div class="form-group">
            <label>Nota (1 a 5)</label>
            <select name="nota" required>
              <option value="">Selecione</option>
              <?php for ($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= $i ?> ★</option><?php endfor; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Comentario</label>
            <input type="text" name="comentario" placeholder="Descreva sua experiencia...">
          </div>
        </div>
        <div style="margin-top:14px"><button type="submit" class="btn btn-primary">Enviar avaliacao</button></div>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <div class="empty-state"><div class="icon">✅</div><p>Nenhum servico pendente de avaliacao.</p></div>
  <?php endif; ?>

  <?php elseif ($aba === 'meusdados'): ?>
  <div class="topbar"><div><h1>👤 Meus Dados</h1><p>Atualize suas informacoes pessoais e senha</p></div></div>
  <div class="card">
    <div class="card-header"><h2>Informacoes pessoais</h2></div>
    <form method="POST">
      <input type="hidden" name="acao" value="salvar_dados">
      <div class="form-grid two">
        <div class="form-group"><label>Nome completo *</label><input type="text" name="nome" value="<?= e($dadosUser['nome'] ?? '') ?>" required></div>
        <div class="form-group"><label>E-mail</label><input type="email" value="<?= e($dadosUser['email'] ?? '') ?>" disabled style="opacity:.6;cursor:not-allowed"></div>
        <div class="form-group"><label>Telefone</label><input type="tel" name="telefone" id="tel_meusdados" value="<?= e($dadosUser['telefone'] ?? '') ?>" placeholder="(44) 99999-9999"></div>
        <div class="form-group"><label>Endereco</label><input type="text" name="endereco" value="<?= e($dadosUser['endereco'] ?? '') ?>"></div>
        <div class="form-group"><label>Cidade</label><input type="text" name="cidade" value="<?= e($dadosUser['cidade'] ?? '') ?>"></div>
        <div class="form-group"><label>Estado</label><input type="text" name="estado" value="<?= e($dadosUser['estado'] ?? '') ?>" placeholder="PR"></div>
      </div>
      <div style="margin-top:16px"><button type="submit" class="btn btn-primary">Salvar alteracoes</button></div>
    </form>
  </div>
  <div class="card">
    <div class="card-header"><h2>Alterar senha</h2></div>
    <form method="POST">
      <input type="hidden" name="acao" value="trocar_senha">
      <div class="form-grid two">
        <div class="form-group"><label>Senha atual *</label><input type="password" name="senha_atual" required></div>
        <div class="form-group"></div>
        <div class="form-group"><label>Nova senha *</label><input type="password" name="senha_nova" required></div>
        <div class="form-group"><label>Confirmar nova senha *</label><input type="password" name="senha_confirm" required></div>
      </div>
      <p style="font-size:12px;color:var(--muted);margin-top:8px">A nova senha deve ter no minimo 8 caracteres, com letra maiuscula, minuscula, numero e caractere especial.</p>
      <div style="margin-top:16px"><button type="submit" class="btn btn-primary">Alterar senha</button></div>
    </form>
  </div>

  <?php endif; ?>

</main>

<?php if ($empSel): ?>
<div class="modal-backdrop open" id="empModal">
  <div class="modal-box" style="max-width:680px">
    <button class="modal-close" onclick="window.location='?aba=buscar<?= $termo ? '&q='.urlencode($termo) : '' ?>'">×</button>
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
      <div style="width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,var(--purple),var(--purple2));display:grid;place-items:center;font-weight:800;font-size:18px;flex-shrink:0;">
        <?= mb_strtoupper(mb_substr($empSel['nome'],0,2)) ?>
      </div>
      <div>
        <h2 style="font-size:22px"><?= e($empSel['nome']) ?></h2>
        <p style="color:var(--muted);font-size:13px"><?= e($empSel['cidade'].', '.$empSel['estado']) ?></p>
        <div class="stars"><?= $empSel['media'] ? formatStars((int)round($empSel['media'])) : '☆☆☆☆☆' ?> <span style="color:var(--muted);font-size:12px"><?= $empSel['media'] ? number_format($empSel['media'],1) : '—' ?> (<?= $empSel['total_av'] ?> av.)</span></div>
      </div>
    </div>
    <p style="color:var(--muted);font-size:14px;margin-bottom:20px"><?= e($empSel['descricao']) ?></p>
    <?php if ($empSel['servicos']): ?>
    <h3 style="margin-bottom:12px;font-size:15px">Servicos oferecidos</h3>
    <p style="color:var(--muted);font-size:12px;margin-bottom:10px">Selecione um ou mais servicos para incluir na solicitacao.</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px" id="servicosGrid">
      <?php foreach ($empSel['servicos'] as $sv): ?>
      <label style="background:rgba(168,85,247,.08);border:1px solid rgba(168,85,247,.2);border-radius:14px;padding:14px;cursor:pointer;display:block;transition:.2s" class="srv-card" data-preco="<?= $sv['preco_medio'] ?? 0 ?>">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
          <input type="checkbox" name="servicos_sel[]" value="<?= e($sv['nome']) ?>" data-preco="<?= $sv['preco_medio'] ?? 0 ?>" style="width:16px;height:16px;accent-color:#a855f7;cursor:pointer" onchange="atualizarTotal()">
          <strong style="font-size:14px"><?= e($sv['nome']) ?></strong>
        </div>
        <p style="color:var(--muted);font-size:12px;margin:4px 0 6px 26px"><?= e($sv['descricao']) ?></p>
        <?php if($sv['preco_medio']): ?><span style="color:#d8b4fe;font-weight:700;font-size:13px;margin-left:26px">R$ <?= number_format($sv['preco_medio'],2,',','.') ?></span><?php endif; ?>
      </label>
      <?php endforeach; ?>
    </div>
    <div id="totalBox" style="display:none;background:rgba(168,85,247,.15);border:1px solid rgba(168,85,247,.3);border-radius:12px;padding:12px 16px;margin-bottom:16px;justify-content:space-between;align-items:center">
      <span style="font-size:13px;color:var(--muted)">Total estimado dos servicos selecionados:</span>
      <strong style="color:#d8b4fe;font-size:16px" id="totalValor">R$ 0,00</strong>
    </div>
    <?php endif; ?>
    <h3 style="margin-bottom:14px;font-size:15px">📩 Solicitar servico</h3>
    <form method="POST" action="?aba=buscar" id="formSolicitar">
      <input type="hidden" name="acao" value="solicitar">
      <input type="hidden" name="empresa_id" value="<?= $empSel['id'] ?>">
      <div class="form-grid">
        <div class="form-group">
          <label>Descreva o que voce precisa *</label>
          <textarea name="descricao" id="descricaoSol" placeholder="Ex: Preciso instalar 3 tomadas no quarto..." required></textarea>
        </div>
        <div class="form-group">
          <label>Prioridade</label>
          <select name="prioridade"><option>Baixa</option><option selected>Media</option><option>Alta</option></select>
        </div>
      </div>
      <div style="margin-top:14px"><button type="submit" class="btn btn-primary">Enviar Solicitacao</button></div>
    </form>
    <?php if ($empSel['avaliacoes']): ?>
    <h3 style="margin:24px 0 12px;font-size:15px">💬 Avaliacoes recentes</h3>
    <?php foreach ($empSel['avaliacoes'] as $av): ?>
    <div style="background:rgba(255,255,255,.05);border-radius:12px;padding:14px;margin-bottom:10px">
      <div class="stars" style="font-size:13px"><?= formatStars($av['nota']) ?></div>
      <p style="color:var(--muted);font-size:13px;margin-top:4px"><?= e($av['comentario'] ?: 'Sem comentario.') ?></p>
      <small style="color:rgba(255,255,255,.35)"><?= e($av['u_nome']) ?> • <?= date('d/m/Y',strtotime($av['data'])) ?></small>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<script>
const telMeusDados = document.getElementById('tel_meusdados');
if (telMeusDados) {
    telMeusDados.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '').substring(0, 11);
        if (v.length <= 10) {
            v = v.replace(/^(\d{2})(\d)/, '($1) $2');
            v = v.replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            v = v.replace(/^(\d{2})(\d)/, '($1) $2');
            v = v.replace(/(\d{5})(\d)/, '$1-$2');
        }
        e.target.value = v;
    });
}

function atualizarTotal() {
    const checks = document.querySelectorAll('input[name="servicos_sel[]"]:checked');
    let total = 0, nomes = [];
    checks.forEach(c => { total += parseFloat(c.dataset.preco) || 0; nomes.push(c.value); });
    const box = document.getElementById('totalBox');
    const val = document.getElementById('totalValor');
    if (checks.length > 0) {
        box.style.display = 'flex';
        val.textContent = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
    } else {
        box.style.display = 'none';
    }
    const desc = document.getElementById('descricaoSol');
    if (desc && nomes.length > 0) { desc.value = 'Servicos: ' + nomes.join(', '); }
    else if (desc) { desc.value = ''; }
    document.querySelectorAll('.srv-card').forEach(card => {
        const cb = card.querySelector('input[type=checkbox]');
        card.style.borderColor = cb.checked ? '#a855f7' : 'rgba(168,85,247,.2)';
        card.style.background  = cb.checked ? 'rgba(168,85,247,.2)' : 'rgba(168,85,247,.08)';
    });
}
</script>
</body>
</html>