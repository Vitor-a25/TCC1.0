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
    $data_conc = ($status === 'Serviço Realizado') ? date('Y-m-d') : null;
    $func_id   = (int)($_POST['funcionario_id'] ?? 0);
    $cat_sol   = (int)($_POST['categoria_sol']  ?? 0); // Categoria classificada pela empresa

    // Novos campos do bloco A
    $desl_val  = trim($_POST['desl_valor']  ?? '');
    $desl_uni  = $_POST['desl_unidade']     ?? 'min';
    $est_val   = trim($_POST['est_valor']   ?? '');
    $est_uni   = $_POST['est_unidade']      ?? 'min';
    $inicio    = trim($_POST['inicio_previsto'] ?? '');
    $fora_hor  = (int)($_POST['fora_horario'] ?? 0);

    // Monta strings legíveis ex: "30 min" ou "2 horas"
    $tempo_desl = $desl_val ? $desl_val . ' ' . $desl_uni : null;
    $tempo_est  = $est_val  ? $est_val  . ' ' . $est_uni  : null;

    if ($sol_id && $func_id) {
        $db->prepare('UPDATE solicitacao SET resposta=?,status=?,data_conclusao=?,funcionario_id=?,tempo_deslocamento=?,tempo_estimado=?,inicio_previsto=?,fora_horario=?,categoria_id=? WHERE id=? AND empresa_id=?')
           ->execute([$resp,$status,$data_conc,$func_id,$tempo_desl,$tempo_est,$inicio,$fora_hor,$cat_sol ?: null,$sol_id,$emp_id]);
        redirect('empresa.php?aba=solicitacoes&ok=Resposta+enviada+com+sucesso');
    } else {
        $msg = 'error:Selecione um funcionário para continuar.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar_func') {
    if ($emp_id) {
        $fn   = trim($_POST['func_nome']  ?? '');
        $ft   = trim($_POST['func_tel']   ?? '');
        $fc   = trim($_POST['func_cargo'] ?? '');
        $fpa  = isset($_POST['pode_atender']) ? 1 : 0; // 1=pode atender sozinho, 0=auxiliar
        $fid  = (int)($_POST['func_id']   ?? 0);
        $cats_sel = $_POST['func_cats'] ?? []; // Array de categoria_ids selecionados

        if ($fn) {
            if ($fid) {
                $db->prepare('UPDATE funcionario SET nome=?,telefone=?,cargo=?,pode_atender=? WHERE id=? AND empresa_id=?')
                   ->execute([$fn,$ft,$fc,$fpa,$fid,$emp_id]);
                // Remove vínculos antigos e reinsere
                $db->prepare('DELETE FROM funcionario_categoria WHERE funcionario_id=?')->execute([$fid]);
            } else {
                $db->prepare('INSERT INTO funcionario (empresa_id,nome,telefone,cargo,pode_atender) VALUES (?,?,?,?,?)')
                   ->execute([$emp_id,$fn,$ft,$fc,$fpa]);
                $fid = $db->lastInsertId();
            }
            // Insere vínculos de categorias
            $ins_cat = $db->prepare('INSERT INTO funcionario_categoria (funcionario_id, categoria_id) VALUES (?,?)');
            foreach ($cats_sel as $cid) {
                $ins_cat->execute([$fid, (int)$cid]);
            }
            $redir = $fid ? 'Funcionario+atualizado' : 'Funcionario+cadastrado';
            redirect('empresa.php?aba=funcionarios&ok=' . $redir);
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
$concluidas   = count(array_filter($solicitacoes, fn($s) => $s['status'] === 'Serviço Realizado'));

$media_av = 0;
if ($emp_id) {
    $av = $db->prepare('SELECT AVG(nota) FROM avaliacao WHERE empresa_id=?');
    $av->execute([$emp_id]);
    $media_av = round((float)$av->fetchColumn(), 1);
}


$top_servicos = [];
if ($emp_id) {
    $ts = $db->prepare('
        SELECT s.nome, COUNT(sol.id) as total
        FROM servico s
        LEFT JOIN solicitacao sol ON sol.empresa_id = s.empresa_id
            AND sol.descricao LIKE CONCAT("%", s.nome, "%")
        WHERE s.empresa_id = ?
        GROUP BY s.id, s.nome
        ORDER BY total DESC
        LIMIT 5
    ');
    $ts->execute([$emp_id]);
    $top_servicos = $ts->fetchAll();
}


$avaliacoes_emp = [];
if ($emp_id) {
    $ae = $db->prepare('
        SELECT a.nota, a.comentario, a.data, u.nome as u_nome
        FROM avaliacao a
        JOIN usuario u ON u.id = a.usuario_id
        WHERE a.empresa_id = ? AND a.moderado = 0
        ORDER BY a.data DESC
        LIMIT 10
    ');
    $ae->execute([$emp_id]);
    $avaliacoes_emp = $ae->fetchAll();
}


$mes_emp = $_GET['mes_emp'] ?? '';
$ano_emp = $_GET['ano_emp'] ?? date('Y');

if ($emp_id) {
    $sql_emp_graf = 'SELECT DATE_FORMAT(data_solicitacao, "%Y-%m") as mes, COUNT(*) as total FROM solicitacao WHERE empresa_id = ? AND YEAR(data_solicitacao) = ?';
    $params_emp_graf = [$emp_id, (int)$ano_emp];
    if ($mes_emp) {
        $sql_emp_graf .= ' AND MONTH(data_solicitacao) = ?';
        $params_emp_graf[] = (int)$mes_emp;
    }
    $sql_emp_graf .= ' GROUP BY mes ORDER BY mes ASC';
    $stmt_emp_graf = $db->prepare($sql_emp_graf);
    $stmt_emp_graf->execute($params_emp_graf);
    $dados_emp_graf = $stmt_emp_graf->fetchAll();
} else {
    $dados_emp_graf = [];
}

$emp_labels = [];
$emp_totais = [];
$nomes_meses_emp = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
$dados_emp_map = [];
foreach ($dados_emp_graf as $d) $dados_emp_map[$d['mes']] = $d['total'];

if ($mes_emp) {
    $chave = sprintf('%04d-%02d', (int)$ano_emp, (int)$mes_emp);
    $emp_labels[] = $nomes_meses_emp[(int)$mes_emp - 1].'/'.$ano_emp;
    $emp_totais[] = $dados_emp_map[$chave] ?? 0;
} else {
    for ($m = 1; $m <= 12; $m++) {
        $chave = sprintf('%04d-%02d', (int)$ano_emp, $m);
        $emp_labels[] = $nomes_meses_emp[$m-1];
        $emp_totais[] = $dados_emp_map[$chave] ?? 0;
    }
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
    // Busca categorias de cada funcionário para uso no modal de resposta (JSON para JS)
    $func_cats_map = [];
    foreach ($funcionarios as $frow) {
        $fc_q = $db->prepare('SELECT categoria_id FROM funcionario_categoria WHERE funcionario_id=?');
        $fc_q->execute([$frow['id']]);
        $func_cats_map[$frow['id']] = $fc_q->fetchAll(PDO::FETCH_COLUMN);
    }
}

$editFunc = null;
if (isset($_GET['edit_func']) && $emp_id) {
    $ef = $db->prepare('SELECT * FROM funcionario WHERE id=? AND empresa_id=?');
    $ef->execute([(int)$_GET['edit_func'], $emp_id]);
    $editFunc = $ef->fetch();
    if ($editFunc) {
        $ec = $db->prepare('SELECT categoria_id FROM funcionario_categoria WHERE funcionario_id=?');
        $ec->execute([$editFunc['id']]);
        $editFunc['cats'] = $ec->fetchAll(PDO::FETCH_COLUMN); // Array de IDs das categorias do funcionário
    }
}

// GESTAO
$rel_status  = $_GET['rel_status'] ?? '';
$rel_func_id = (int)($_GET['rel_func'] ?? 0);
$rel_sols = [];
if ($emp_id && $aba === 'gestao') {
    $sql_rel = 'SELECT s.*, u.nome as u_nome, u.telefone as u_tel, f.nome as func_nome, f.cargo as func_cargo
                FROM solicitacao s
                JOIN usuario u ON u.id = s.usuario_id
                LEFT JOIN funcionario f ON f.id = s.funcionario_id
                WHERE s.empresa_id = ?';
    $params_rel = [$emp_id];
    if ($rel_status) { $sql_rel .= ' AND s.status = ?'; $params_rel[] = $rel_status; }
    if ($rel_func_id) { $sql_rel .= ' AND s.funcionario_id = ?'; $params_rel[] = $rel_func_id; }
    $sql_rel .= ' ORDER BY s.data_solicitacao DESC';
    $stmt_rel = $db->prepare($sql_rel);
    $stmt_rel->execute($params_rel);
    $rel_sols = $stmt_rel->fetchAll();
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
<style>
@media print {
  .sidebar,.menu-toggle,.no-print{display:none!important}
  .main{margin-left:0!important;padding:0!important}
  .print-only{display:block!important}
  body{background:#fff!important;color:#000!important}
  .card{box-shadow:none!important;border:1px solid #ddd!important;background:#fff!important;color:#000!important}
  table{width:100%;border-collapse:collapse}
  th,td{border:1px solid #ccc;padding:6px 8px;font-size:12px;color:#000!important}
  th{background:#f0f0f0!important}
  .badge{border:1px solid #999!important;background:#eee!important;color:#000!important;padding:2px 6px;border-radius:4px;font-size:11px}
}
.print-only{display:none}
</style>
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
    <a href="?aba=gestao" class="<?= $aba==='gestao'?'active':'' ?>"><span class="icon">📋</span> Gestão</a>
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

  <div class="card" style="margin-bottom:20px">
    <div class="card-header">
      <h2>📈 Solicitações por mês</h2>
      <form method="GET" style="display:flex;gap:10px;align-items:center">
        <input type="hidden" name="aba" value="painel">
        <select name="mes_emp" style="padding:6px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#000;font-size:13px">
          <option value="">Todos os meses</option>
          <?php foreach (['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'] as $i => $mn): ?>
            <option value="<?= $i+1 ?>" <?= $mes_emp==($i+1)?'selected':'' ?>><?= $mn ?></option>
          <?php endforeach; ?>
        </select>
        <select name="ano_emp" style="padding:6px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#000;font-size:13px">
          <?php for ($y = date('Y'); $y >= date('Y')-3; $y--): ?>
            <option value="<?= $y ?>" <?= $ano_emp==$y?'selected':'' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
      </form>
    </div>
    <div style="position:relative;height:240px;padding:10px 0">
      <canvas id="graficoEmpSols"></canvas>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px">

    <div class="card">
      <div class="card-header"><h2>📊 Serviços mais solicitados</h2></div>
      <?php if ($top_servicos && array_sum(array_column($top_servicos, 'total')) > 0): ?>
        <?php $max = max(array_column($top_servicos, 'total')) ?: 1; ?>
        <div style="padding:8px 0;max-height:280px;overflow-y:auto">
          <?php foreach ($top_servicos as $ts): ?>
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
              <span><?= e($ts['nome']) ?></span>
              <span style="color:var(--muted)"><?= $ts['total'] ?> sol.</span>
            </div>
            <div style="background:var(--line);border-radius:999px;height:8px;overflow:hidden">
              <div style="background:var(--purple);height:8px;border-radius:999px;width:<?= round(($ts['total'] / $max) * 100) ?>%;transition:width .4s"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state"><div class="icon">📊</div><p>Nenhuma solicitação registrada ainda.</p></div>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="card-header"><h2>⭐ Avaliações recebidas</h2></div>
      <?php if ($avaliacoes_emp): ?>
      <div style="max-height:280px;overflow-y:auto">
      <table>
        <thead style="position:sticky;top:0;background:var(--card);z-index:1"><tr><th>Cliente</th><th>Nota</th><th>Comentário</th><th>Data</th></tr></thead>
        <tbody>
        <?php foreach ($avaliacoes_emp as $av): ?>
          <tr>
            <td><?= e($av['u_nome']) ?></td>
            <td style="color:#facc15;white-space:nowrap"><?= str_repeat('★', $av['nota']) . str_repeat('☆', 5 - $av['nota']) ?></td>
            <td style="color:var(--muted);font-size:13px"><?= e($av['comentario'] ?: '—') ?></td>
            <td style="white-space:nowrap;font-size:12px;color:var(--muted)"><?= date('d/m/Y', strtotime($av['data'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
      <?php else: ?>
        <div class="empty-state"><div class="icon">⭐</div><p>Nenhuma avaliação recebida ainda.</p></div>
      <?php endif; ?>
    </div>

  </div>

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
        <!-- Toggle: pode atender sozinho? -->
        <div class="form-group" style="display:flex;align-items:center;gap:12px;padding-top:8px">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0">
            <input type="checkbox" name="pode_atender" value="1"
              <?= ($editFunc['pode_atender'] ?? 1) ? 'checked' : '' ?>
              style="width:18px;height:18px;accent-color:var(--purple);cursor:pointer">
            <span>
              <strong style="display:block;font-size:14px">Pode atender sozinho</strong>
              <small style="color:var(--muted);font-size:12px">Desmarque para auxiliares que não atendem sem supervisão</small>
            </span>
          </label>
        </div>
      </div>

      <!-- Categorias que o funcionário atende -->
      <div class="form-group" style="margin-top:18px">
        <label style="margin-bottom:10px;display:block">📂 Categorias de serviço que este funcionário atende</label>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
          <?php foreach ($cats as $c):
            $checked = in_array($c['id'], $editFunc['cats'] ?? []) ? 'checked' : '';
          ?>
          <label style="display:flex;align-items:center;gap:10px;background:var(--card);border:1px solid var(--line);border-radius:12px;padding:12px;cursor:pointer;transition:.2s"
                 onmouseover="this.style.borderColor='var(--purple)'" onmouseout="this.style.borderColor='var(--line)'">
            <input type="checkbox" name="func_cats[]" value="<?= $c['id'] ?>" <?= $checked ?>
                   style="width:16px;height:16px;accent-color:var(--purple);cursor:pointer">
            <span><?= $c['icone'] ?> <?= e($c['nome']) ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="margin-top:18px;display:flex;gap:10px">
        <button type="submit" class="btn btn-primary"><?= $editFunc ? 'Salvar edição' : 'Cadastrar funcionário' ?></button>
        <?php if ($editFunc): ?><a href="?aba=funcionarios" class="btn btn-outline">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>
  <div class="card">
    <div class="card-header"><h2>Funcionários Cadastrados</h2></div>
    <?php if ($funcionarios): ?>
    <table>
      <thead><tr><th>Nome</th><th>Cargo</th><th>Atende sozinho</th><th>Categorias</th><th>Telefone</th><th>Ações</th></tr></thead>
      <tbody>
      <?php foreach ($funcionarios as $f):
        $fc_q2 = $db->prepare('SELECT c.icone, c.nome FROM funcionario_categoria fc JOIN categoria c ON c.id=fc.categoria_id WHERE fc.funcionario_id=?');
        $fc_q2->execute([$f['id']]);
        $f_cats = $fc_q2->fetchAll();
      ?>
        <tr>
          <td><strong><?= e($f['nome']) ?></strong></td>
          <td><?= e($f['cargo'] ?? '—') ?></td>
          <td style="text-align:center">
            <?php if ($f['pode_atender']): ?>
              <span class="badge badge-done">✅ Sim</span>
            <?php else: ?>
              <span class="badge badge-pending">Auxiliar</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($f_cats): ?>
              <div style="display:flex;flex-wrap:wrap;gap:4px">
              <?php foreach ($f_cats as $fc): ?>
                <span style="background:rgba(168,85,247,.15);border:1px solid rgba(168,85,247,.3);border-radius:8px;padding:2px 8px;font-size:11px"><?= $fc['icone'] ?> <?= e($fc['nome']) ?></span>
              <?php endforeach; ?>
              </div>
            <?php else: ?>
              <span style="color:var(--muted);font-size:12px">Nenhuma</span>
            <?php endif; ?>
          </td>
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
      $sr = $db->prepare('SELECT s.*,u.nome as u_nome,u.telefone as u_tel,COALESCE(emp_sol.endereco, u.endereco) as u_endereco,COALESCE(emp_sol.cidade, u.cidade) as u_cidade,COALESCE(emp_sol.estado, u.estado) as u_estado FROM solicitacao s JOIN usuario u ON u.id=s.usuario_id LEFT JOIN empresa emp_sol ON emp_sol.usuario_id=u.id WHERE s.id=? AND s.empresa_id=?');
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
          <td><?php $sc=['Pendente'=>'badge-pending','Em andamento'=>'badge-progress','Serviço Realizado'=>'badge-done','Cancelado'=>'badge-cancelled']; ?><span class="badge <?= $sc[$s['status']]??'' ?>"><?= e($s['status']) ?></span></td>
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
      <form method="POST" id="formResponder">
        <input type="hidden" name="acao" value="responder">
        <input type="hidden" name="solicitacao_id" value="<?= $solResp['id'] ?>">
        <input type="hidden" name="inicio_previsto" id="inicio_previsto_hidden">
        <input type="hidden" name="fora_horario" id="fora_horario_hidden" value="0">

        <div class="form-grid">
          <!-- Status -->
          <div class="form-group"><label>Atualizar status</label>
            <select name="status">
              <option value="Pendente"     <?= $solResp['status']==='Pendente'?'selected':'' ?>>Pendente</option>
              <option value="Em andamento" <?= $solResp['status']==='Em andamento'?'selected':'' ?>>Em andamento</option>
              <option value="Cancelado"    <?= $solResp['status']==='Cancelado'?'selected':'' ?>>Cancelado</option>
            </select>
          </div>

          <!-- Categoria da solicitação (empresa classifica) -->
          <div class="form-group">
            <label>📂 Categoria do serviço</label>
            <select name="categoria_sol" id="categoria_sol" onchange="filtrarFuncionarios()">
              <option value="">Selecione para filtrar funcionários...</option>
              <?php foreach ($cats as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($solResp['categoria_id']??0)==$c['id']?'selected':'' ?>>
                  <?= $c['icone'] ?> <?= e($c['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small style="color:var(--muted);font-size:11px;margin-top:4px;display:block">Selecione a categoria para ver os funcionários disponíveis</small>
          </div>

          <!-- Funcionário (filtrado por categoria e pode_atender) -->
          <div class="form-group">
            <label>👷 Funcionário responsável *</label>
            <?php if ($funcionarios): ?>
            <!-- data-cats armazena as categorias do funcionário em JSON para o JS filtrar -->
            <select name="funcionario_id" id="select_funcionario" required>
              <option value="">Selecione a categoria primeiro...</option>
              <?php foreach ($funcionarios as $f):
                $f_cats_ids = $func_cats_map[$f['id']] ?? [];
              ?>
                <option value="<?= $f['id'] ?>"
                        data-pode="<?= $f['pode_atender'] ?>"
                        data-cats="<?= e(implode(',', $f_cats_ids)) ?>"
                        <?= ($solResp['funcionario_id']??0)==$f['id']?'selected':'' ?>>
                  <?= e($f['nome']) ?><?= $f['cargo'] ? ' — '.$f['cargo'] : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small id="avisoFuncionario" style="color:var(--muted);font-size:11px;margin-top:4px;display:none">
              ⚠️ Nenhum funcionário disponível para esta categoria. Verifique os cadastros.
            </small>
            <?php else: ?>
              <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:10px;font-size:13px;color:#f87171;">
                ⚠️ Nenhum funcionário cadastrado. <a href="?aba=funcionarios" style="color:#f87171;text-decoration:underline;">Cadastrar agora</a>
              </div>
            <?php endif; ?>
          </div>

          <!-- Tempo de deslocamento -->
          <div class="form-group">
            <label>🚗 Tempo de deslocamento</label>
            <div style="display:flex;gap:8px">
              <input type="number" name="desl_valor" id="desl_valor" min="1" max="999" placeholder="Ex: 30"
                     value="<?= e(preg_replace('/[^0-9]/', '', $solResp['tempo_deslocamento'] ?? '')) ?>"
                     style="flex:1" oninput="calcularInicio()">
              <select name="desl_unidade" id="desl_unidade" style="width:110px" onchange="calcularInicio()">
                <option value="min"  <?= str_contains($solResp['tempo_deslocamento']??'','min')?'selected':'' ?>>minutos</option>
                <option value="hora" <?= str_contains($solResp['tempo_deslocamento']??'','hora')?'selected':'' ?>>horas</option>
              </select>
            </div>
          </div>

          <!-- Tempo estimado do serviço -->
          <div class="form-group">
            <label>⏱️ Tempo estimado do serviço</label>
            <div style="display:flex;gap:8px">
              <input type="number" name="est_valor" min="1" max="999" placeholder="Ex: 2"
                     value="<?= e(preg_replace('/[^0-9]/', '', $solResp['tempo_estimado'] ?? '')) ?>"
                     style="flex:1">
              <select name="est_unidade" style="width:110px">
                <option value="min"  <?= str_contains($solResp['tempo_estimado']??'','min')?'selected':'' ?>>minutos</option>
                <option value="hora" <?= str_contains($solResp['tempo_estimado']??'','hora')?'selected':'' ?>>horas</option>
              </select>
            </div>
          </div>

          <!-- Horário de início previsto (calculado automaticamente) -->
          <div class="form-group" id="boxInicio" style="display:none">
            <label>🕐 Horário de início previsto</label>
            <div id="inicioPrevisto" style="background:rgba(168,85,247,.12);border:1px solid rgba(168,85,247,.3);border-radius:12px;padding:12px 16px;font-size:18px;font-weight:700;color:var(--purple);letter-spacing:2px">
              --:--
            </div>
            <small style="color:var(--muted);font-size:11px;margin-top:4px;display:block">Calculado automaticamente: hora atual + deslocamento, arredondado para a dezena.</small>
          </div>

          <!-- Aviso fora do horário -->
          <div id="avisoHorario" style="display:none;background:rgba(234,179,8,.1);border:1px solid rgba(234,179,8,.4);border-radius:12px;padding:14px;font-size:13px;color:#fbbf24;">
            ⚠️ O horário previsto ultrapassa as 18h. O cliente será avisado que o serviço pode não ser concluído hoje.
            <label style="display:flex;align-items:center;gap:8px;margin-top:10px;color:#fff;cursor:pointer">
              <input type="checkbox" id="confirmarForaHorario" style="width:16px;height:16px;accent-color:#a855f7">
              Entendi, desejo continuar mesmo assim
            </label>
          </div>

          <!-- Observação (opcional, por último) -->
          <div class="form-group" style="grid-column:1/-1">
            <label>💬 Observação para o cliente <span style="color:var(--muted);font-size:12px">(opcional)</span></label>
            <textarea name="resposta" placeholder="Escreva algo se necessário..."><?= e($solResp['resposta'] ?? '') ?></textarea>
          </div>
        </div>

        <div style="margin-top:14px">
          <button type="button" class="btn btn-primary" id="btnEnviarResp" <?= !$funcionarios ? 'disabled' : '' ?> onclick="confirmarEnvio()">
            Enviar resposta
          </button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <?php elseif ($aba === 'gestao'): ?>
  <div class="topbar no-print"><div><h1>📋 Gestão</h1><p>Relatórios de serviços e funcionários</p></div></div>

  <div class="card no-print">
    <div class="card-header"><h2>Filtros</h2></div>
    <form method="GET" style="display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end">
      <input type="hidden" name="aba" value="gestao">
      <div class="form-group" style="flex:1;min-width:180px">
        <label>Status</label>
        <select name="rel_status">
          <option value="">Todos os status</option>
          <option value="Pendente" <?= $rel_status==='Pendente'?'selected':'' ?>>Pendente</option>
          <option value="Em andamento" <?= $rel_status==='Em andamento'?'selected':'' ?>>Em andamento</option>
          <option value="Servico Realizado" <?= $rel_status==='Servico Realizado'?'selected':'' ?>>Servico Realizado</option>
          <option value="Cancelado" <?= $rel_status==='Cancelado'?'selected':'' ?>>Cancelado</option>
        </select>
      </div>
      <div class="form-group" style="flex:1;min-width:180px">
        <label>Funcionário</label>
        <select name="rel_func">
          <option value="">Todos os funcionários</option>
          <?php foreach ($funcionarios as $f): ?>
            <option value="<?= $f['id'] ?>" <?= $rel_func_id===$f['id']?'selected':'' ?>><?= e($f['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="?aba=gestao" class="btn btn-outline">Limpar</a>
      </div>
    </form>
  </div>

  <div class="print-only" style="margin-bottom:20px">
    <h2 style="font-size:20px;margin-bottom:4px">OPUS — Relatório de Gestão</h2>
    <?php
      $fn_sel = null;
      if ($rel_func_id) {
          $fn_arr = array_values(array_filter($funcionarios, fn($f) => $f['id'] === $rel_func_id));
          $fn_sel = $fn_arr[0] ?? null;
      }
    ?>
    <p style="font-size:13px;color:#666">
      Empresa: <?= e($empresa['nome'] ?? '') ?> |
      <?php if ($rel_status): ?>Status: <?= e($rel_status) ?> | <?php endif; ?>
      <?php if ($fn_sel): ?>Funcionário: <?= e($fn_sel['nome']) ?> | <?php endif; ?>
      Gerado em: <?= date('d/m/Y H:i') ?>
    </p>
    <hr style="margin:10px 0;border:1px solid #ccc">
  </div>

  <div class="card">
    <div class="card-header">
      <h2>Solicitações<?php if ($rel_status): ?> — <?= e($rel_status) ?><?php endif; ?>
        <?php if ($fn_sel): ?> — <?= e($fn_sel['nome']) ?><?php endif; ?>
        <span style="font-size:13px;color:var(--muted);font-weight:400">(<?= count($rel_sols) ?> registro<?= count($rel_sols) != 1 ? 's' : '' ?>)</span>
      </h2>
      <button onclick="window.print()" class="btn btn-primary no-print">🖨️ Imprimir</button>
    </div>
    <?php if ($rel_sols): ?>
    <table>
      <thead><tr><th>#</th><th>Cliente</th><th>Telefone</th><th>Descrição</th><th>Status</th><th>Funcionário</th><th>Data Solicitação</th><th>Data Conclusão</th></tr></thead>
      <tbody>
      <?php foreach ($rel_sols as $r): ?>
        <tr>
          <td><?= $r['id'] ?></td>
          <td><?= e($r['u_nome']) ?></td>
          <td style="white-space:nowrap"><?= e($r['u_tel'] ?? '—') ?></td>
          <td style="max-width:180px"><?= e(mb_strimwidth($r['descricao'],0,50,'...')) ?></td>
          <td><?php $sc=['Pendente'=>'badge-pending','Em andamento'=>'badge-progress','Servico Realizado'=>'badge-done','Cancelado'=>'badge-cancelled']; ?>
            <span class="badge <?= $sc[$r['status']]??'' ?>"><?= e($r['status'] ?: '—') ?></span></td>
          <td><?= $r['func_nome'] ? e($r['func_nome']).($r['func_cargo']?' ('.e($r['func_cargo']).')':'') : '—' ?></td>
          <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($r['data_solicitacao'])) ?></td>
          <td style="white-space:nowrap"><?= $r['data_conclusao'] ? date('d/m/Y', strtotime($r['data_conclusao'])) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="empty-state"><div class="icon">📋</div><p>Nenhuma solicitação encontrada com os filtros selecionados.</p></div>
    <?php endif; ?>
  </div>

  <?php endif; ?>

</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<script>
const empLabels = <?= json_encode($emp_labels) ?>;
const empTotais = <?= json_encode($emp_totais) ?>;
const ctxEmp = document.getElementById('graficoEmpSols');
if (ctxEmp) {
    new Chart(ctxEmp, {
        type: 'bar',
        data: {
            labels: empLabels,
            datasets: [{
                label: 'Solicitações',
                data: empTotais,
                backgroundColor: 'rgba(168,85,247,0.7)',
                borderColor: 'rgba(168,85,247,1)',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: 'rgba(255,255,255,0.5)', stepSize: 1 },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                },
                x: {
                    ticks: { color: 'rgba(255,255,255,0.5)' },
                    grid: { display: false }
                }
            } 
        }
    });
}


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

// ============================================================
// Bloco B: Filtra funcionários por categoria e pode_atender
// ============================================================
function filtrarFuncionarios() {
    const catSel  = document.getElementById('categoria_sol');
    const funcSel = document.getElementById('select_funcionario');
    const aviso   = document.getElementById('avisoFuncionario');
    if (!catSel || !funcSel) return;

    const catId = catSel.value; // ID da categoria selecionada
    const opts  = funcSel.querySelectorAll('option');
    let visiveis = 0;

    opts.forEach(opt => {
        if (!opt.value) return; // Pula o placeholder

        const pode  = opt.dataset.pode;   // "1" ou "0"
        const cats  = opt.dataset.cats;   // "1,3,5" (IDs separados por vírgula)
        const catsArr = cats ? cats.split(',') : [];

        // Mostra só quem pode atender sozinho E tem aquela categoria (ou se nenhuma categoria selecionada)
        const atendeCategoria = !catId || catsArr.includes(catId);
        const podeAtender     = pode === '1';
        const visivel         = podeAtender && atendeCategoria;

        opt.style.display = visivel ? '' : 'none';
        if (visivel) visiveis++;
    });

    // Reseta seleção se o funcionário atual ficou oculto
    const selOpt = funcSel.options[funcSel.selectedIndex];
    if (selOpt && selOpt.style.display === 'none') {
        funcSel.value = '';
    }

    // Mostra aviso se nenhum funcionário disponível
    if (aviso) aviso.style.display = visiveis === 0 ? 'block' : 'none';

    // Atualiza placeholder
    funcSel.options[0].text = catId
        ? (visiveis > 0 ? 'Selecione o funcionário...' : 'Nenhum disponível para esta categoria')
        : 'Selecione a categoria primeiro...';
}

// Roda ao carregar se já houver categoria salva
document.addEventListener('DOMContentLoaded', function() {
    const catSel = document.getElementById('categoria_sol');
    if (catSel && catSel.value) filtrarFuncionarios();
});

// ============================================================
// Bloco A: Cálculo do horário de início previsto
// Pega hora atual + tempo de deslocamento, arredonda para a dezena
// ============================================================
function calcularInicio() {
    const valEl = document.getElementById('desl_valor');
    const uniEl = document.getElementById('desl_unidade');
    if (!valEl || !uniEl) return;

    const val = parseInt(valEl.value);
    if (!val || val <= 0) {
        document.getElementById('boxInicio').style.display = 'none';
        document.getElementById('avisoHorario').style.display = 'none';
        document.getElementById('inicio_previsto_hidden').value = '';
        document.getElementById('fora_horario_hidden').value = '0';
        return;
    }

    // Converte para minutos
    const minutos = uniEl.value === 'hora' ? val * 60 : val;

    // Pega hora atual do computador
    const agora = new Date();
    const totalMin = agora.getHours() * 60 + agora.getMinutes() + minutos;

    // Arredonda para a dezena acima (ex: 21 → 30, 49 → 50, 50 → 50)
    const minArred = Math.ceil(totalMin / 10) * 10;

    const horas = Math.floor(minArred / 60) % 24;
    const mins  = minArred % 60;
    const horaFmt = String(horas).padStart(2, '0') + ':' + String(mins).padStart(2, '0');

    // Exibe o horário calculado
    document.getElementById('boxInicio').style.display = 'block';
    document.getElementById('inicioPrevisto').textContent = horaFmt;
    document.getElementById('inicio_previsto_hidden').value = horaFmt;

    // Verifica se ultrapassa 18h
    const foraHorario = horas >= 18;
    document.getElementById('avisoHorario').style.display = foraHorario ? 'block' : 'none';
    document.getElementById('fora_horario_hidden').value = foraHorario ? '1' : '0';

    if (!foraHorario) {
        const cb = document.getElementById('confirmarForaHorario');
        if (cb) cb.checked = false;
    }
}

// Confirma o envio: se fora do horário, exige que a empresa marque o checkbox
function confirmarEnvio() {
    const foraHorario = document.getElementById('fora_horario_hidden').value === '1';
    if (foraHorario) {
        const cb = document.getElementById('confirmarForaHorario');
        if (!cb || !cb.checked) {
            alert('Por favor, confirme que deseja continuar mesmo com o horário acima das 18h.');
            return;
        }
    }
    document.getElementById('formResponder').submit();
}
</script>
</body>
</html>