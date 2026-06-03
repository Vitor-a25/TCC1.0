<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin('empresa');

$db     = getDB();
$uid    = $_SESSION['usuario_id'];
$nome   = $_SESSION['nome'];
$emp_id = $_SESSION['empresa_id'] ?? null;
$msg    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar_empresa') {
    $n    = trim($_POST['nome_emp']  ?? '');
    $cnpj = trim($_POST['cnpj']      ?? '');
    $em   = trim($_POST['email_emp'] ?? '');
    $tel  = trim($_POST['tel_emp']   ?? '');
    $end  = trim($_POST['endereco']  ?? '');
    $cid  = trim($_POST['cidade']    ?? '');
    $est  = trim($_POST['estado']    ?? '');
    $desc = trim($_POST['descricao'] ?? '');
    if (!$n || !$desc) {
        $msg = 'error:Preencha ao menos nome e descrição.';
    } else {
        if ($emp_id) {
            $db->prepare('UPDATE empresa SET nome=?,cnpj=?,email=?,telefone=?,endereco=?,cidade=?,estado=?,descricao=? WHERE id=?')
               ->execute([$n,$cnpj,$em,$tel,$end,$cid,$est,$desc,$emp_id]);
            $msg = 'success:Perfil atualizado com sucesso!';
        } else {
            $db->prepare('INSERT INTO empresa (usuario_id,nome,cnpj,email,telefone,endereco,cidade,estado,descricao) VALUES (?,?,?,?,?,?,?,?,?)')
               ->execute([$uid,$n,$cnpj,$em,$tel,$end,$cid,$est,$desc]);
            $emp_id = $db->lastInsertId();
            $_SESSION['empresa_id'] = $emp_id;
            $msg = 'success:Empresa cadastrada com sucesso!';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar_servico') {
    if (!$emp_id) { $msg = 'error:Cadastre sua empresa primeiro.'; }
    else {
        $sn  = trim($_POST['nome_srv']    ?? '');
        $sd  = trim($_POST['desc_srv']    ?? '');
        $cat = (int)($_POST['categoria_id'] ?? 0);
        $pm = str_replace(',', '.', str_replace('.', '', $_POST['preco'] ?? ''));
        $sid = (int)($_POST['servico_id']  ?? 0);
        if (!$sn) { $msg = 'error:Informe o nome do serviço.'; }
        elseif (!$cat) { $msg = 'error:Selecione uma categoria para o serviço.'; }
        elseif ($sid) {
            $db->prepare('UPDATE servico SET nome=?,descricao=?,categoria_id=?,preco_medio=? WHERE id=? AND empresa_id=?')
               ->execute([$sn,$sd,$cat ?: null,$pm ?: null,$sid,$emp_id]);
            redirect('empresa.php?aba=servicos&ok=Servico+atualizado+com+sucesso');
        } else {
            $db->prepare('INSERT INTO servico (empresa_id,categoria_id,nome,descricao,preco_medio) VALUES (?,?,?,?,?)')
               ->execute([$emp_id,$cat ?: null,$sn,$sd,$pm ?: null]);
            redirect('empresa.php?aba=servicos&ok=Servico+cadastrado+com+sucesso');
        }
    }
}

if ($_GET['del_srv'] ?? null) {
    $db->prepare('DELETE FROM servico WHERE id=? AND empresa_id=?')->execute([(int)$_GET['del_srv'],$emp_id]);
    redirect('empresa.php?aba=servicos');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'responder') {
    $sol_id    = (int)($_POST['solicitacao_id'] ?? 0);
    $resp      = trim($_POST['resposta'] ?? '');
    $status    = $_POST['status'] ?? 'Em andamento';
    $data_conc = ($status === 'Concluído') ? date('Y-m-d') : null;
    $func_id = (int)($_POST['funcionario_id'] ?? 0);
    if ($sol_id && $resp && $func_id) {
        $db->prepare('UPDATE solicitacao SET resposta=?,status=?,data_conclusao=?,funcionario_id=? WHERE id=? AND empresa_id=?')
           ->execute([$resp,$status,$data_conc,$func_id,$sol_id,$emp_id]);
        redirect('empresa.php?aba=solicitacoes&ok=Resposta+enviada+com+sucesso');
    } else {
        $msg = 'error:Preencha a resposta e selecione um funcionário.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar_func') {
    if ($emp_id) {
        $fn   = trim($_POST['func_nome']     ?? '');
        $ft   = trim($_POST['func_tel']      ?? '');
        $fc   = trim($_POST['func_cargo']    ?? '');
        $fid  = (int)($_POST['func_id']      ?? 0);
        if ($fn) {
            if ($fid) {
                $db->prepare('UPDATE funcionario SET nome=?,telefone=?,cargo=? WHERE id=? AND empresa_id=?')
                   ->execute([$fn,$ft,$fc,$fid,$emp_id]);
                redirect('empresa.php?aba=funcionarios&ok=Funcionario+atualizado');
            } else {
                $db->prepare('INSERT INTO funcionario (empresa_id,nome,telefone,cargo) VALUES (?,?,?,?)')
                   ->execute([$emp_id,$fn,$ft,$fc]);
                redirect('empresa.php?aba=funcionarios&ok=Funcionario+cadastrado');
            }
        }
    }
}

if ($_GET['del_func'] ?? null) {
    $db->prepare('DELETE FROM funcionario WHERE id=? AND empresa_id=?')->execute([(int)$_GET['del_func'],$emp_id]);
    redirect('empresa.php?aba=funcionarios&ok=Funcionario+removido');
}

$aba = $_GET['aba'] ?? 'painel';
if (isset($_GET['ok'])) $msg = 'success:' . urldecode($_GET['ok']);

$empresa = null;
if ($emp_id) {
    $e = $db->prepare('SELECT * FROM empresa WHERE id=?');
    $e->execute([$emp_id]);
    $empresa = $e->fetch();
}

$cats = $db->query('SELECT * FROM categoria ORDER BY nome')->fetchAll();

$servicos = [];
if ($emp_id) {
    $s = $db->prepare('SELECT s.*, c.nome as cat_nome FROM servico s LEFT JOIN categoria c ON c.id=s.categoria_id WHERE s.empresa_id=? ORDER BY s.criado_em DESC');
    $s->execute([$emp_id]);
    $servicos = $s->fetchAll();
}

$solicitacoes = [];
if ($emp_id) {
    $s = $db->prepare('SELECT s.*, u.nome as u_nome, u.telefone as u_tel FROM solicitacao s JOIN usuario u ON u.id=s.usuario_id WHERE s.empresa_id=? ORDER BY s.data_solicitacao DESC');
    $s->execute([$emp_id]);
    $solicitacoes = $s->fetchAll();
}

$total_sols   = count($solicitacoes);
$pendentes    = count(array_filter($solicitacoes, fn($s) => $s['status'] === 'Pendente'));
$em_andamento = count(array_filter($solicitacoes, fn($s) => $s['status'] === 'Em andamento'));
$concluidas   = count(array_filter($solicitacoes, fn($s) => $s['status'] === 'Concluído'));

$media_av = 0;
if ($emp_id) {
    $av = $db->prepare('SELECT AVG(nota) FROM avaliacao WHERE empresa_id=?');
    $av->execute([$emp_id]);
    $media_av = round((float)$av->fetchColumn(), 1);
}

$editSrv = null;
if (isset($_GET['edit_srv']) && $emp_id) {
    $es = $db->prepare('SELECT * FROM servico WHERE id=? AND empresa_id=?');
    $es->execute([(int)$_GET['edit_srv'], $emp_id]);
    $editSrv = $es->fetch();
}

$funcionarios = [];
if ($emp_id) {
    $f = $db->prepare('SELECT * FROM funcionario WHERE empresa_id=? AND ativo=1 ORDER BY nome');
    $f->execute([$emp_id]);
    $funcionarios = $f->fetchAll();
}

$editFunc = null;
if (isset($_GET['edit_func']) && $emp_id) {
    $ef = $db->prepare('SELECT * FROM funcionario WHERE id=? AND empresa_id=?');
    $ef->execute([(int)$_GET['edit_func'], $emp_id]);
    $editFunc = $ef->fetch();
}

[$msgTipo, $msgText] = $msg ? explode(':', $msg, 2) : ['', ''];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Painel Empresa | OPUS</title>
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
    <span><?= $empresa ? e($empresa['nome']) : 'Empresa não cadastrada' ?></span>
    <div class="sidebar-badge empresa">Empresa</div>
  </div>
  <nav class="sidebar-nav">
    <a href="?aba=painel"       class="<?= $aba==='painel'?'active':'' ?>"><span class="icon">📊</span> Painel</a>
    <a href="?aba=perfil"       class="<?= $aba==='perfil'?'active':'' ?>"><span class="icon">🏢</span> Minha Empresa</a>
    <a href="?aba=servicos"     class="<?= $aba==='servicos'?'active':'' ?>"><span class="icon">🛠️</span> Serviços</a>
    <a href="usuario.php"><span class="icon">🔍</span> Buscar Serviços</a>
    <a href="?aba=funcionarios"  class="<?= $aba==='funcionarios'?'active':'' ?>"><span class="icon">👷</span> Funcionários</a>
    <a href="?aba=solicitacoes" class="<?= $aba==='solicitacoes'?'active':'' ?>"><span class="icon">📩</span> Solicitações <?php if($pendentes): ?><span style="background:var(--yellow);color:#000;border-radius:999px;padding:1px 8px;font-size:11px;font-weight:700;margin-left:4px"><?= $pendentes ?></span><?php endif; ?></a>
  </nav>
  <div class="sidebar-footer">
    <a href="logout.php">🚪 Sair</a>
  </div>
</aside>

<main class="main">

  <?php if ($msgText): ?>
    <div class="alert alert-<?= $msgTipo === 'success' ? 'success' : 'error' ?>"><?= e($msgText) ?></div>
  <?php endif; ?>

  <?php if (!$empresa && $aba !== 'perfil'): ?>
    <div class="alert alert-info">⚠️ Você ainda não cadastrou sua empresa. <a href="?aba=perfil" style="color:#93c5fd;text-decoration:underline;">Cadastrar agora</a></div>
  <?php endif; ?>

  <?php if ($aba === 'painel'): ?>
  <div class="topbar"><div><h1>📊 Painel</h1><p>Resumo das suas atividades</p></div></div>
  <div class="stats-row">
    <div class="stat-card"><div class="label">Total de Solicitações</div><div class="value"><?= $total_sols ?></div></div>
    <div class="stat-card"><div class="label">Pendentes</div><div class="value" style="color:var(--yellow)"><?= $pendentes ?></div></div>
    <div class="stat-card"><div class="label">Em Andamento</div><div class="value" style="color:var(--blue)"><?= $em_andamento ?></div></div>
    <div class="stat-card"><div class="label">Avaliação Média</div><div class="value" style="color:#facc15"><?= $media_av ?: '—' ?> <span style="font-size:20px">★</span></div></div>
  </div>

  <?php $ultsols = array_slice(array_filter($solicitacoes, fn($s)=>$s['status']==='Pendente'), 0, 5); ?>
  <?php if ($ultsols): ?>
  <div class="card">
    <div class="card-header"><h2>🔔 Solicitações Pendentes</h2>
    <a href="?aba=solicitacoes" class="btn btn-outline btn-sm">Ver todas</a></div>
    <table>
      <thead><tr><th>Cliente</th><th>Descrição</th><th>Prioridade</th><th>Data</th><th>Ação</th></tr></thead>
      <tbody>
      <?php foreach ($ultsols as $s): ?>
        <tr>
          <td><?= e($s['u_nome']) ?></td>
          <td><?= e(mb_strimwidth($s['descricao'],0,60,'...')) ?></td>
          <td><span class="badge <?= $s['prioridade']==='Alta'?'badge-cancelled':($s['prioridade']==='Média'?'badge-progress':'badge-active') ?>"><?= e($s['prioridade']) ?></span></td>
          <td><?= date('d/m/Y',strtotime($s['data_solicitacao'])) ?></td>
          <td><a href="?aba=solicitacoes&responder=<?= $s['id'] ?>" class="btn btn-primary btn-sm">Responder</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php elseif ($aba === 'perfil'): ?>
  <div class="topbar"><div><h1>🏢 Minha Empresa</h1><p>Gerencie as informações da sua empresa</p></div></div>
  <div class="card">
    <form method="POST">
      <input type="hidden" name="acao" value="salvar_empresa">
      <div class="form-grid two">
        <div class="form-group"><label>Nome da empresa *</label><input type="text" name="nome_emp" value="<?= e($empresa['nome'] ?? '') ?>" required></div>
        <div class="form-group"><label>CNPJ</label><input type="text" name="cnpj" id="cnpj_emp" value="<?= e($empresa['cnpj'] ?? '') ?>" placeholder="00.000.000/0001-00" maxlength="18"></div>
        <div class="form-group"><label>E-mail de contato</label><input type="email" name="email_emp" value="<?= e($empresa['email'] ?? '') ?>"></div>
        <div class="form-group"><label>Telefone</label><input type="tel" name="tel_emp" value="<?= e($empresa['telefone'] ?? '') ?>"></div>
        <div class="form-group"><label>Endereço</label><input type="text" name="endereco" value="<?= e($empresa['endereco'] ?? '') ?>"></div>
        <div class="form-group"><label>Cidade</label><input type="text" name="cidade" value="<?= e($empresa['cidade'] ?? '') ?>"></div>
        <div class="form-group"><label>Estado</label><input type="text" name="estado" value="<?= e($empresa['estado'] ?? '') ?>" placeholder="PR"></div>
      </div>
      <div class="form-group" style="margin-top:4px"><label>Descrição da empresa *</label><textarea name="descricao" placeholder="Descreva os serviços e diferenciais da sua empresa..." required><?= e($empresa['descricao'] ?? '') ?></textarea></div>
      <div style="margin-top:16px"><button type="submit" class="btn btn-primary"><?= $empresa ? 'Salvar alterações' : 'Cadastrar empresa' ?></button></div>
    </form>
  </div>

  <?php elseif ($aba === 'servicos'): ?>
  <div class="topbar"><div><h1>🛠️ Serviços</h1><p>Adicione e edite seus serviços</p></div></div>
  <div class="card">
    <div class="card-header"><h2><?= $editSrv ? 'Editar Serviço' : 'Novo Serviço' ?></h2></div>
    <form method="POST">
      <input type="hidden" name="acao" value="salvar_servico">
      <?php if ($editSrv): ?><input type="hidden" name="servico_id" value="<?= $editSrv['id'] ?>"><?php endif; ?>
      <div class="form-grid two">
        <div class="form-group"><label>Nome do serviço *</label><input type="text" name="nome_srv" value="<?= e($editSrv['nome'] ?? '') ?>" required></div>
        <div class="form-group"><label>Categoria</label>
          <select name="categoria_id" required>
            <option value="">Selecione uma categoria *</option>
            <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= (($editSrv['categoria_id']??0)==$c['id'])?'selected':'' ?>><?= e($c['icone'].' '.$c['nome']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Preço médio (R$)</label><input type="text" name="preco" id="preco" placeholder="0,00" value="<?= $editSrv['preco_medio'] ?? '' ?>"></div>
        <div class="form-group"><label>Descrição</label><input type="text" name="desc_srv" value="<?= e($editSrv['descricao'] ?? '') ?>" placeholder="Breve descrição do serviço"></div>
      </div>
      <div style="margin-top:14px;display:flex;gap:10px">
        <button type="submit" class="btn btn-primary"><?= $editSrv ? 'Salvar edição' : 'Adicionar serviço' ?></button>
        <?php if ($editSrv): ?><a href="?aba=servicos" class="btn btn-outline">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>
  <div class="card">
    <div class="card-header"><h2>Serviços Cadastrados</h2></div>
    <?php if ($servicos): ?>
    <table>
      <thead><tr><th>Nome</th><th>Categoria</th><th>Preço Médio</th><th>Descrição</th><th>Ações</th></tr></thead>
      <tbody>
      <?php foreach ($servicos as $sv): ?>
        <tr>
          <td><strong><?= e($sv['nome']) ?></strong></td>
          <td><?= e($sv['cat_nome'] ?? '—') ?></td>
          <td><?= $sv['preco_medio'] ? 'R$ '.number_format($sv['preco_medio'],2,',','.') : '—' ?></td>
          <td style="color:var(--muted)"><?= e(mb_strimwidth($sv['descricao'],0,60,'...')) ?></td>
          <td>
            <div class="btn-group">
              <a href="?aba=servicos&edit_srv=<?= $sv['id'] ?>" class="btn btn-outline btn-sm">✏️ Editar</a>
              <a href="?del_srv=<?= $sv['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remover este serviço?')">🗑️</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="empty-state"><div class="icon">🛠️</div><p>Nenhum serviço cadastrado ainda.</p></div>
    <?php endif; ?>
  </div>

  <?php elseif ($aba === 'funcionarios'): ?>
  <div class="topbar"><div><h1>👷 Funcionários</h1><p>Gerencie os funcionários da sua empresa</p></div></div>
  <div class="card">
    <div class="card-header"><h2><?= $editFunc ? 'Editar Funcionário' : 'Novo Funcionário' ?></h2></div>
    <form method="POST">
      <input type="hidden" name="acao" value="salvar_func">
      <?php if ($editFunc): ?><input type="hidden" name="func_id" value="<?= $editFunc['id'] ?>"><?php endif; ?>
      <div class="form-grid two">
        <div class="form-group"><label>Nome *</label><input type="text" name="func_nome" value="<?= e($editFunc['nome'] ?? '') ?>" required placeholder="Nome completo"></div>
        <div class="form-group"><label>Telefone</label><input type="tel" name="func_tel" id="func_tel" value="<?= e($editFunc['telefone'] ?? '') ?>" placeholder="(44) 99999-9999"></div>
        <div class="form-group"><label>Cargo</label><input type="text" name="func_cargo" value="<?= e($editFunc['cargo'] ?? '') ?>" placeholder="Ex: Técnico, Eletricista..."></div>
      </div>
      <div style="margin-top:14px;display:flex;gap:10px">
        <button type="submit" class="btn btn-primary"><?= $editFunc ? 'Salvar edição' : 'Cadastrar funcionário' ?></button>
        <?php if ($editFunc): ?><a href="?aba=funcionarios" class="btn btn-outline">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>
  <div class="card">
    <div class="card-header"><h2>Funcionários Cadastrados</h2></div>
    <?php if ($funcionarios): ?>
    <table>
      <thead><tr><th>Nome</th><th>Cargo</th><th>Telefone</th><th>Ações</th></tr></thead>
      <tbody>
      <?php foreach ($funcionarios as $f): ?>
        <tr>
          <td><strong><?= e($f['nome']) ?></strong></td>
          <td><?= e($f['cargo'] ?? '—') ?></td>
          <td><?= e($f['telefone'] ?? '—') ?></td>
          <td>
            <div class="btn-group">
              <a href="?aba=funcionarios&edit_func=<?= $f['id'] ?>" class="btn btn-outline btn-sm">✏️ Editar</a>
              <a href="?del_func=<?= $f['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remover este funcionário?')">🗑️</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="empty-state"><div class="icon">👷</div><p>Nenhum funcionário cadastrado ainda.</p></div>
    <?php endif; ?>
  </div>

  <?php elseif ($aba === 'solicitacoes'): ?>
  <div class="topbar"><div><h1>📩 Solicitações</h1><p>Responda as solicitações dos seus clientes</p></div></div>

  <?php
  $responder_id = (int)($_GET['responder'] ?? 0);
  $solResp = null;
  if ($responder_id && $emp_id) {
      $sr = $db->prepare('SELECT s.*,u.nome as u_nome,u.telefone as u_tel,u.endereco as u_endereco,u.cidade as u_cidade,u.estado as u_estado FROM solicitacao s JOIN usuario u ON u.id=s.usuario_id WHERE s.id=? AND s.empresa_id=?');
      $sr->execute([$responder_id, $emp_id]);
      $solResp = $sr->fetch();
  }
  ?>

  <div class="card">
    <table>
      <thead><tr><th>Cliente</th><th>Descrição</th><th>Prioridade</th><th>Status</th><th>Data</th><th>Ação</th></tr></thead>
      <tbody>
      <?php if ($solicitacoes): foreach ($solicitacoes as $s): ?>
        <tr>
          <td>
            <strong><?= e($s['u_nome']) ?></strong><br>
            <small style="color:var(--muted)"><?= e($s['u_tel']) ?></small>
          </td>
          <td style="max-width:180px"><?= e(mb_strimwidth($s['descricao'],0,70,'...')) ?></td>
          <td><span class="badge <?= $s['prioridade']==='Alta'?'badge-cancelled':($s['prioridade']==='Média'?'badge-progress':'badge-active') ?>"><?= e($s['prioridade']) ?></span></td>
          <td><?php $sc=['Pendente'=>'badge-pending','Em andamento'=>'badge-progress','Concluído'=>'badge-done','Cancelado'=>'badge-cancelled']; ?><span class="badge <?= $sc[$s['status']]??'' ?>"><?= e($s['status']) ?></span></td>
          <td><?= date('d/m/Y',strtotime($s['data_solicitacao'])) ?></td>
          <td><a href="?aba=solicitacoes&responder=<?= $s['id'] ?>" class="btn btn-primary btn-sm">Responder</a></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="6"><div class="empty-state"><div class="icon">📭</div><p>Nenhuma solicitação recebida.</p></div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($solResp): ?>
  <div class="modal-backdrop open">
    <div class="modal-box">
      <button class="modal-close" onclick="window.location='?aba=solicitacoes'">×</button>
      <h2>Responder Solicitação</h2>
      <div style="background:rgba(168,85,247,.08);border:1px solid rgba(168,85,247,.2);border-radius:14px;padding:14px;margin-bottom:20px">
        <strong>Cliente:</strong> <?= e($solResp['u_nome']) ?> — <?= e($solResp['u_tel']) ?><br>
<?php if ($solResp['u_endereco']): ?>
<strong>Endereço:</strong> <?= e($solResp['u_endereco']) ?><?= $solResp['u_cidade'] ? ', ' . e($solResp['u_cidade']) : '' ?><?= $solResp['u_estado'] ? ' - ' . e($solResp['u_estado']) : '' ?><br>
<?php endif; ?>
<strong>Prioridade:</strong> <?= e($solResp['prioridade']) ?><br>
<strong>Descrição:</strong> <?= e($solResp['descricao']) ?>
      </div>
      <?php if ($solResp['resposta']): ?>
        <div class="alert alert-info">Resposta atual: <?= e($solResp['resposta']) ?></div>
      <?php endif; ?>
      <form method="POST">
        <input type="hidden" name="acao" value="responder">
        <input type="hidden" name="solicitacao_id" value="<?= $solResp['id'] ?>">
        <div class="form-grid">
          <div class="form-group"><label>Resposta / Orçamento *</label><textarea name="resposta" required><?= e($solResp['resposta'] ?? 'Olá! Recebemos sua solicitação e já estamos a caminho.') ?></textarea>
          <div class="form-group"><label>Atualizar status</label>
            <select name="status">
              <option value="Pendente"     <?= $solResp['status']==='Pendente'?'selected':'' ?>>Pendente</option>
              <option value="Em andamento" <?= $solResp['status']==='Em andamento'?'selected':'' ?>>Em andamento</option>
              <option value="Concluído"    <?= $solResp['status']==='Concluído'?'selected':'' ?>>Concluído</option>
              <option value="Cancelado"    <?= $solResp['status']==='Cancelado'?'selected':'' ?>>Cancelado</option>
            </select>
          </div>
          <div class="form-group">
            <label>👷 Funcionário responsável *</label>
            <?php if ($funcionarios): ?>
            <select name="funcionario_id" required>
              <option value="">Selecione o funcionário...</option>
              <?php foreach ($funcionarios as $f): ?>
                <option value="<?= $f['id'] ?>" <?= ($solResp['funcionario_id']??0)==$f['id']?'selected':'' ?>>
                  <?= e($f['nome']) ?><?= $f['cargo'] ? ' — '.$f['cargo'] : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php else: ?>
              <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:10px;font-size:13px;color:#f87171;">
                ⚠️ Nenhum funcionário cadastrado. <a href="?aba=funcionarios" style="color:#f87171;text-decoration:underline;">Cadastrar agora</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div style="margin-top:14px"><button type="submit" class="btn btn-primary"<?= !$funcionarios ? ' disabled' : '' ?>>Enviar resposta</button></div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <?php endif; ?>

</main>

<script>
const precoEl = document.getElementById('preco');
if (precoEl) {
    precoEl.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '');
        if (v === '') { e.target.value = ''; return; }
        let num = parseInt(v);
        let centavos = String(num % 100).padStart(2, '0');
        let reais = String(Math.floor(num / 100));
        reais = reais.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        e.target.value = reais + ',' + centavos;
    });
}

function mascaraTelFunc() {
    const el = document.getElementById('func_tel');
    if (!el) return;
    el.addEventListener('input', function(e) {
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
mascaraTelFunc();

const cnpjEmp = document.getElementById('cnpj_emp');
if (cnpjEmp) {
    cnpjEmp.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '');
        v = v.replace(/^(\d{2})(\d)/, '$1.$2');
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
        v = v.replace(/(\d{4})(\d)/, '$1-$2');
        e.target.value = v;
    });
}
</script>
</body>
</html>