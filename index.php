<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$db = getDB();
$cats = $db->query('SELECT * FROM categoria ORDER BY nome')->fetchAll();

$servicos_home = $db->query('
    SELECT s.nome, s.descricao, s.preco_medio, c.icone, c.nome as cat_nome
    FROM servico s
    JOIN categoria c ON c.id = s.categoria_id
    WHERE s.ativo = 1
    ORDER BY RAND()
    LIMIT 6
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>OPUS | Plataforma de Serviços</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <header class="header">
    <a href="index.php" class="logo">OPUS</a>
    <button class="menu-btn" id="menuBtn" aria-label="Abrir menu">☰</button>
    <nav class="nav" id="navMenu">
      <a href="#inicio">Início</a>
      <a href="#servicos">Serviços</a>
      <a href="#empresas">Empresas</a>
      <a href="#como-funciona">Como funciona</a>
      <a href="#avaliacoes">Avaliações</a>
      <?php if (isLoggedIn()):
        $painel = getTipo() === 'admin' ? 'admin.php' : (getTipo() === 'empresa' ? 'empresa.php' : 'usuario.php');
      ?>
        <a href="<?= $painel ?>" class="btn-login">⚙️ Meu painel</a>
      <?php else: ?>
        <a href="cadastro.php">Criar conta</a>
        <a href="login.php" class="btn-login">Entrar</a>
      <?php endif; ?>
    </nav>
  </header>

  <main>
    <section class="hero" id="inicio">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <span class="tag">Plataforma de Serviços</span>
        <h1>Encontre empresas e profissionais confiáveis em poucos cliques.</h1>
        <p>O OPUS conecta você a empresas prestadoras de serviços. Busque por categoria, veja avaliações, solicite atendimento e acompanhe tudo em um só lugar.</p>
        <div class="hero-actions">
          <a href="#servicos" class="btn primary">Buscar serviços</a>
          <?php if (!isLoggedIn()): ?>
            <a href="cadastro.php" class="btn secondary">Criar conta grátis</a>
          <?php endif; ?>
        </div>
        <div class="hero-stats">
          <div><strong>+50</strong><span>profissionais</span></div>
          <div><strong>+200</strong><span>serviços realizados</span></div>
          <div><strong>Web</strong><span>responsivo</span></div>
        </div>
      </div>
    </section>


    <section class="search-panel" id="servicos">
      <div class="section-title">
        <span>Busca rápida</span>
        <h2>Pesquise por serviços ou categorias</h2>
        <p>Encontre o profissional certo para o que você precisa.</p>
      </div>


      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Ex: eletricista, pintura, frete..." />
        <select id="categoryFilter">
          <option value="todos">Todas as categorias</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= $c['id'] ?>"><?= $c['icone'] ?> <?= htmlspecialchars($c['nome']) ?></option>
          <?php endforeach; ?>
        </select>
        <button id="searchBtn">Buscar</button>
      </div>


      <div class="services-grid" id="servicesGrid">
        <?php foreach ($servicos_home as $sv): ?>
          <article class="service-card" onclick="window.location='<?= isLoggedIn() ? 'usuario.php?aba=buscar' : 'login.php' ?>'" style="cursor:pointer">
            <div class="service-icon"><?= $sv['icone'] ?></div>
            <h3><?= htmlspecialchars($sv['nome']) ?></h3>
            <p><?= htmlspecialchars(mb_strimwidth($sv['descricao'], 0, 80, '...')) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="companies" id="empresas">
      <div class="section-title">
        <span>Empresas</span>
        <h2>Conheça nossos prestadores</h2>
        <p>Veja os dados da empresa, serviços oferecidos, reputação e solicite atendimento.</p>
      </div>


      <div class="company-grid">
        <article class="company-card featured">
          <div class="company-top"><div class="avatar">EL</div><div><h3>EletroLuz Cianorte</h3><p>Instalações elétricas</p></div></div>
          <p>Atendimento residencial e comercial, manutenção preventiva, troca de disjuntores e instalação de tomadas.</p>
          <div class="rating">★★★★★ <span>4.9 • 128 avaliações</span></div>
          <?php if (isLoggedIn()): ?>
            <a href="usuario.php?aba=buscar&empresa=1" style="display:block;text-align:center;padding:14px;border-radius:12px;background:linear-gradient(135deg,#a855f7,#7c3aed);color:#fff;font-weight:700;">Solicitar serviço</a>
          <?php else: ?>
            <a href="login.php" style="display:block;text-align:center;padding:14px;border-radius:12px;background:linear-gradient(135deg,#a855f7,#7c3aed);color:#fff;font-weight:700;">Solicitar serviço</a>
          <?php endif; ?>
        </article>


        <article class="company-card">
          <div class="company-top"><div class="avatar">FR</div><div><h3>Fretes Paraná</h3><p>Transporte e pequenas mudanças</p></div></div>
          <p>Equipe especializada em fretes urbanos, montagem simples e transporte seguro.</p>
          <div class="rating">★★★★☆ <span>4.7 • 82 avaliações</span></div>
          <?php if (isLoggedIn()): ?>
            <a href="usuario.php?aba=buscar&empresa=2" style="display:block;text-align:center;padding:14px;border-radius:12px;background:linear-gradient(135deg,#a855f7,#7c3aed);color:#fff;font-weight:700;">Solicitar serviço</a>
          <?php else: ?>
            <a href="login.php" style="display:block;text-align:center;padding:14px;border-radius:12px;background:linear-gradient(135deg,#a855f7,#7c3aed);color:#fff;font-weight:700;">Solicitar serviço</a>
          <?php endif; ?>
        </article>


        <article class="company-card">
          <div class="company-top"><div class="avatar">TI</div><div><h3>TechHelp</h3><p>Informática e suporte</p></div></div>
          <p>Formatação, manutenção, instalação de sistemas e suporte técnico para empresas.</p>
          <div class="rating">★★★★★ <span>4.8 • 94 avaliações</span></div>
          <?php if (isLoggedIn()): ?>
            <a href="usuario.php?aba=buscar&empresa=3" style="display:block;text-align:center;padding:14px;border-radius:12px;background:linear-gradient(135deg,#a855f7,#7c3aed);color:#fff;font-weight:700;">Solicitar serviço</a>
          <?php else: ?>
            <a href="login.php" style="display:block;text-align:center;padding:14px;border-radius:12px;background:linear-gradient(135deg,#a855f7,#7c3aed);color:#fff;font-weight:700;">Solicitar serviço</a>
          <?php endif; ?>
        </article>
      </div>
    </section>


    <section class="workflow" id="como-funciona">
      <div class="section-title">
        <span>Como funciona</span>
        <h2>Simples, rápido e seguro</h2>
        <p>Em poucos passos você encontra o profissional ideal e acompanha tudo pela plataforma.</p>
      <div class="steps" style="margin-top:40px">
  <div class="step"><h3>Criar conta</h3><p>Cadastre-se gratuitamente como cliente ou empresa prestadora de serviços.</p></div>
  <div class="step"><h3>Buscar serviço</h3><p>Pesquise por categoria, palavra-chave ou nome da empresa.</p></div>
  <div class="step"><h3>Solicitar atendimento</h3><p>Descreva o que você precisa e envie direto para a empresa escolhida.</p></div>
</div>
<div style="display:flex;justify-content:center;margin-top:24px">
  <div class="step" style="max-width:600px;text-align:center"><h3>Avaliar</h3><p>Após a conclusão, avalie o atendimento e ajude outros usuários.</p></div>
</div>
    </section>


    <section class="dashboards">
      <div class="section-title">
        <span>Para todos os perfis</span>
        <h2>Uma plataforma, duas experiências</h2>
      </div>
      <div class="dashboard-grid" style="display:grid;grid-template-columns:1fr 1fr;max-width:700px;margin:0 auto;gap:44px">
        <div class="panel"><h3>🙋 Cliente</h3><ul><li>Buscar serviços</li><li>Visualizar empresas</li><li>Solicitar atendimento</li><li>Avaliar prestadores</li></ul></div>
        <div class="panel"><h3>🏢 Empresa</h3><ul><li>Cadastrar perfil</li><li>Gerenciar serviços</li><li>Responder solicitações</li><li>Acompanhar avaliações</li></ul></div>
      </div>
    </section>


    <section class="reviews" id="avaliacoes">
      <div class="section-title">
        <span>Avaliações</span>
        <h2>O que dizem nossos usuários</h2>
      </div>
      <div class="review-grid">
        <blockquote>"Encontrei um eletricista rapidamente e consegui acompanhar a resposta da empresa." <span>★★★★★ — Cliente</span></blockquote>
        <blockquote>"A plataforma organiza as solicitações e facilita o contato com novos clientes." <span>★★★★★ — Empresa</span></blockquote>
        <blockquote>"Interface simples, rápida e objetiva para comparar prestadores." <span>★★★★☆ — Usuário</span></blockquote>
      </div>
    </section>

    
    <section class="cta">
      <h2>Pronto para solicitar um serviço?</h2>
      <p>Crie sua conta gratuitamente e encontre profissionais de confiança.</p>
      <?php if (isLoggedIn()):
        $painel = getTipo() === 'admin' ? 'admin.php' : (getTipo() === 'empresa' ? 'empresa.php' : 'usuario.php');
      ?>
        <a href="<?= $painel ?>" class="btn primary">Ir para o painel</a>
      <?php else: ?>
        <a href="cadastro.php" class="btn primary">Começar agora</a>
      <?php endif; ?>
    </section>
  </main>

  <footer>
    <strong>OPUS</strong>
    <p>Conectando clientes a profissionais de confiança.</p>
  </footer>

  <script src="script.js"></script>
</body>
</html> 