<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) redirect('usuario.php');

$erro    = '';
$sucesso = '';
$tipo    = $_GET['tipo'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo    = $_POST['tipo'] ?? '';
    $nome    = trim($_POST['nome']     ?? '');
    $email   = trim($_POST['email']    ?? '');
    $tel     = trim($_POST['telefone'] ?? '');
    $senha   = $_POST['senha']         ?? '';
    $confirm = $_POST['confirm']       ?? '';

    if (!$nome || !$email || !$senha) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha deve ter no mínimo 8 caracteres.';
    } elseif (!preg_match('/[A-Z]/', $senha)) {
        $erro = 'A senha deve conter ao menos uma letra maiúscula.';
    } elseif (!preg_match('/[a-z]/', $senha)) {
        $erro = 'A senha deve conter ao menos uma letra minúscula.';
    } elseif (!preg_match('/[0-9]/', $senha)) {
        $erro = 'A senha deve conter ao menos um número.';
    } elseif (!preg_match('/[\W_]/', $senha)) {
        $erro = 'A senha deve conter ao menos um caractere especial (ex: @, #, !, $).';
    } elseif ($senha !== $confirm) {
        $erro = 'As senhas não coincidem.';
    } else {
        $db  = getDB();
        $chk = $db->prepare('SELECT id FROM usuario WHERE email = ?');
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            $hash   = password_hash($senha, PASSWORD_DEFAULT);
            $tipoDB = ($tipo === 'empresa') ? 'empresa' : 'cliente';

            // Endereço do cliente
            $end = trim($_POST['endereco'] ?? '');
            $cid = trim($_POST['cidade']   ?? '');
            $est = trim($_POST['estado']   ?? '');

            $ins = $db->prepare('INSERT INTO usuario (nome, email, senha, telefone, endereco, cidade, estado, tipo) VALUES (?,?,?,?,?,?,?,?)');
            $ins->execute([$nome, $email, $hash, $tel, $end, $cid, $est, $tipoDB]);
            $uid = $db->lastInsertId();

            if ($tipoDB === 'empresa') {
                $nome_emp = trim($_POST['nome_emp'] ?? '');
                $cnpj     = trim($_POST['cnpj']     ?? '');
                $end_emp  = trim($_POST['endereco_emp'] ?? '');
                $cid_emp  = trim($_POST['cidade_emp']   ?? '');
                $est_emp  = trim($_POST['estado_emp']   ?? '');
                $desc     = trim($_POST['descricao'] ?? '');
                $db->prepare('INSERT INTO empresa (usuario_id, nome, cnpj, email, telefone, endereco, cidade, estado, descricao) VALUES (?,?,?,?,?,?,?,?,?)')
                   ->execute([$uid, $nome_emp, $cnpj, $email, $tel, $end_emp, $cid_emp, $est_emp, $desc]);
            }

            $sucesso = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastro | OPUS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="auth.css">
<style>
  .tipo-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:10px}
  .tipo-card{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;padding:32px 20px;border-radius:22px;border:2px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);text-decoration:none;color:#fff;transition:.3s;cursor:pointer}
  .tipo-card:hover{border-color:#a855f7;background:rgba(168,85,247,.15);transform:translateY(-4px)}
  .tipo-card .icon{font-size:42px}
  .tipo-card strong{font-size:17px}
  .tipo-card span{font-size:12px;color:rgba(255,255,255,.6);text-align:center}
  .back-link{display:inline-flex;align-items:center;gap:6px;color:rgba(255,255,255,.5);font-size:13px;margin-bottom:18px;text-decoration:none}
  .back-link:hover{color:#fff}
  .senha-wrap{position:relative}
  .senha-wrap input{padding-right:48px}
  .senha-toggle{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:rgba(255,255,255,.5);cursor:pointer;font-size:18px;padding:4px}
  .senha-toggle:hover{color:#fff}
  .requisitos{display:grid;gap:4px;margin-top:10px;padding-left:4px}
  .requisito{font-size:12px;color:#ef4444;transition:color .3s;line-height:1.6}
  .requisito.ok{color:#22c55e}
  .cep-loading{font-size:12px;color:rgba(255,255,255,.4);margin-top:4px;min-height:16px}
</style>
</head>
<body>
<main class="auth">

  <?php if ($sucesso): ?>
  <section class="auth-card">
    <a href="index.php" class="brand">OPUS</a>
    <div style="text-align:center;padding:20px 0">
      <div style="font-size:56px;margin-bottom:16px">🎉</div>
      <h1 style="margin-bottom:10px">Conta criada!</h1>
      <p>Seu cadastro foi realizado com sucesso. Faça login para acessar a plataforma.</p>
      <a href="login.php" style="display:inline-block;margin-top:28px;padding:15px 36px;border-radius:999px;background:linear-gradient(135deg,#a855f7,#7c3aed);color:#fff;font-weight:700;font-size:16px;">Fazer login</a>
    </div>
  </section>

  <?php elseif (!$tipo): ?>
  <section class="auth-card large">
    <a href="index.php" class="brand">OPUS</a>
    <h1>Criar conta</h1>
    <p>Como você quer se cadastrar na plataforma?</p>
    <div class="tipo-grid">
      <a href="?tipo=cliente" class="tipo-card">
        <span class="icon">🙋</span>
        <strong>Sou Cliente</strong>
        <span>Quero buscar serviços e contratar empresas</span>
      </a>
      <a href="?tipo=empresa" class="tipo-card">
        <span class="icon">🏢</span>
        <strong>Sou Empresa</strong>
        <span>Quero divulgar meus serviços e atender clientes</span>
      </a>
    </div>
    <span style="margin-top:28px">Já possui conta? <a href="login.php">Entrar</a></span>
  </section>

  <?php elseif ($tipo === 'cliente'): ?>
  <section class="auth-card large">
    <a href="index.php" class="brand">OPUS</a>
    <a href="cadastro.php" class="back-link">← Voltar</a>
    <h1>Cadastro de Cliente</h1>
    <p>Preencha seus dados básicos para começar a buscar serviços.</p>

    <?php if ($erro): ?>
      <div style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#f87171;padding:12px 16px;border-radius:12px;margin-bottom:16px;font-size:14px;text-align:center;"><?= e($erro) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="tipo" value="cliente">
      <div class="two">
        <label>Nome completo *
          <input type="text" name="nome" placeholder="Seu nome" required value="<?= e($_POST['nome'] ?? '') ?>">
        </label>
        <label>Telefone
          <input type="tel" name="telefone" id="tel_cliente" placeholder="(44) 99999-9999" value="<?= e($_POST['telefone'] ?? '') ?>">
        </label>
      </div>
      <label>E-mail *
        <input type="email" name="email" placeholder="Digite seu e-mail" required value="<?= e($_POST['email'] ?? '') ?>">
      </label>
      <label>Senha *
        <div class="senha-wrap">
          <input type="password" name="senha" id="senha_cliente" placeholder="Crie uma senha" required>
          <button type="button" class="senha-toggle" onclick="toggleSenha('senha_cliente', this)">👁️</button>
        </div>
        <div class="requisitos">
          <div class="requisito" id="rc_tam">Mínimo 8 caracteres</div>
          <div class="requisito" id="rc_mai">Letra maiúscula (A-Z)</div>
          <div class="requisito" id="rc_min">Letra minúscula (a-z)</div>
          <div class="requisito" id="rc_num">Um número (0-9)</div>
          <div class="requisito" id="rc_esp">Um caractere especial (@, #, !, $...)</div>
        </div>
      </label>
      <label>Confirmar senha *
        <div class="senha-wrap">
          <input type="password" name="confirm" id="confirm_cliente" placeholder="Repita a senha" required>
          <button type="button" class="senha-toggle" onclick="toggleSenha('confirm_cliente', this)">👁️</button>
        </div>
      </label>

      <p style="font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px;margin:8px 0 4px">Endereço</p>
      <div class="two" style="align-items:start">
        <label>CEP
          <input type="text" name="cep_cliente" id="cep_cliente" placeholder="00000-000" maxlength="9" value="<?= e($_POST['cep_cliente'] ?? '') ?>">
          <div class="cep-loading" id="cep_status_cliente"></div>
        </label>
        <label style="margin-top:0">Número
          <input type="text" name="numero_cliente" id="numero_cliente" placeholder="Ex: 123" value="<?= e($_POST['numero_cliente'] ?? '') ?>">
        </label>
      </div>
      <label>Endereço
        <input type="text" name="endereco" id="endereco_cliente" placeholder="Preenchido automaticamente pelo CEP" value="<?= e($_POST['endereco'] ?? '') ?>">
      </label>
      <div class="two">
        <label>Cidade
          <input type="text" name="cidade" id="cidade_cliente" placeholder="Preenchida pelo CEP" value="<?= e($_POST['cidade'] ?? '') ?>">
        </label>
        <label>Estado
          <input type="text" name="estado" id="estado_cliente" placeholder="PR" maxlength="2" value="<?= e($_POST['estado'] ?? '') ?>">
        </label>
      </div>

      <button type="submit">Criar conta</button>
    </form>
    <span>Já possui conta? <a href="login.php">Entrar</a></span>
  </section>

  <?php elseif ($tipo === 'empresa'): ?>
  <section class="auth-card large">
    <a href="index.php" class="brand">OPUS</a>
    <a href="cadastro.php" class="back-link">← Voltar</a>
    <h1>Cadastro de Empresa</h1>
    <p>Preencha os dados da sua empresa para começar a receber clientes.</p>

    <?php if ($erro): ?>
      <div style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#f87171;padding:12px 16px;border-radius:12px;margin-bottom:16px;font-size:14px;text-align:center;"><?= e($erro) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="tipo" value="empresa">
      <p style="font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Dados de acesso</p>
      <div class="two">
        <label>Nome do responsável *
          <input type="text" name="nome" placeholder="Seu nome completo" required value="<?= e($_POST['nome'] ?? '') ?>">
        </label>
        <label>Telefone *
          <input type="tel" name="telefone" id="tel_empresa" placeholder="(44) 99999-9999" required value="<?= e($_POST['telefone'] ?? '') ?>">
        </label>
      </div>
      <label>E-mail *
        <input type="email" name="email" placeholder="E-mail para login e contato" required value="<?= e($_POST['email'] ?? '') ?>">
      </label>
      <label>Senha *
        <div class="senha-wrap">
          <input type="password" name="senha" id="senha_empresa" placeholder="Crie uma senha" required>
          <button type="button" class="senha-toggle" onclick="toggleSenha('senha_empresa', this)">👁️</button>
        </div>
        <div class="requisitos">
          <div class="requisito" id="re_tam">Mínimo 8 caracteres</div>
          <div class="requisito" id="re_mai">Letra maiúscula (A-Z)</div>
          <div class="requisito" id="re_min">Letra minúscula (a-z)</div>
          <div class="requisito" id="re_num">Um número (0-9)</div>
          <div class="requisito" id="re_esp">Um caractere especial (@, #, !, $...)</div>
        </div>
      </label>
      <label>Confirmar senha *
        <div class="senha-wrap">
          <input type="password" name="confirm" id="confirm_empresa" placeholder="Repita a senha" required>
          <button type="button" class="senha-toggle" onclick="toggleSenha('confirm_empresa', this)">👁️</button>
        </div>
      </label>

      <p style="font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px;margin:8px 0 4px">Dados da empresa</p>
      <div class="two">
        <label>Nome da empresa *
          <input type="text" name="nome_emp" placeholder="Nome fantasia" required value="<?= e($_POST['nome_emp'] ?? '') ?>">
        </label>
        <label>CNPJ
          <input type="text" name="cnpj" id="cnpj" placeholder="00.000.000/0001-00" maxlength="18" value="<?= e($_POST['cnpj'] ?? '') ?>">
        </label>
      </div>
      <label>Descrição dos serviços *
        <textarea name="descricao" placeholder="Descreva o que sua empresa oferece..." required style="width:100%;padding:14px;border-radius:16px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.1);color:#fff;outline:0;min-height:90px;font-family:inherit;font-size:14px"><?= e($_POST['descricao'] ?? '') ?></textarea>
      </label>

      <p style="font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px;margin:8px 0 4px">Endereço</p>
      <div class="two" style="align-items:start">
        <label>CEP
          <input type="text" name="cep" id="cep" placeholder="00000-000" maxlength="9" value="<?= e($_POST['cep'] ?? '') ?>">
          <div class="cep-loading" id="cep_status"></div>
        </label>
        <label style="margin-top:0">Número
          <input type="text" name="numero" id="numero" placeholder="Ex: 123" value="<?= e($_POST['numero'] ?? '') ?>">
        </label>
      </div>
      <label>Endereço
        <input type="text" name="endereco_emp" id="endereco" placeholder="Preenchido automaticamente pelo CEP" value="<?= e($_POST['endereco_emp'] ?? '') ?>">
      </label>
      <div class="two">
        <label>Cidade
          <input type="text" name="cidade_emp" id="cidade" placeholder="Preenchida pelo CEP" value="<?= e($_POST['cidade_emp'] ?? '') ?>">
        </label>
        <label>Estado
          <input type="text" name="estado_emp" id="estado" placeholder="PR" maxlength="2" value="<?= e($_POST['estado_emp'] ?? '') ?>">
        </label>
      </div>

      <button type="submit">Cadastrar empresa</button>
    </form>
    <span>Já possui conta? <a href="login.php">Entrar</a></span>
  </section>

  <?php endif; ?>

</main>
<script>
function toggleSenha(id, btn) {
    const el = document.getElementById(id);
    if (!el) return;
    if (el.type === 'password') {
        el.type = 'text';
        btn.textContent = '🙈';
    } else {
        el.type = 'password';
        btn.textContent = '👁️';
    }
}

function mascaraTel(id) {
    const el = document.getElementById(id);
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
mascaraTel('tel_cliente');
mascaraTel('tel_empresa');

const cnpjEl = document.getElementById('cnpj');
if (cnpjEl) {
    cnpjEl.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '');
        v = v.replace(/^(\d{2})(\d)/, '$1.$2');
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
        v = v.replace(/(\d{4})(\d)/, '$1-$2');
        e.target.value = v;
    });
}

function validarSenha(inputId, prefixo) {
    const el = document.getElementById(inputId);
    if (!el) return;
    el.addEventListener('input', function() {
        const v = el.value;
        const checks = {
            tam: v.length >= 8,
            mai: /[A-Z]/.test(v),
            min: /[a-z]/.test(v),
            num: /[0-9]/.test(v),
            esp: /[\W_]/.test(v)
        };
        Object.keys(checks).forEach(k => {
            const item = document.getElementById(prefixo + '_' + k);
            if (!item) return;
            item.classList.toggle('ok', checks[k]);
        });
    });
}
validarSenha('senha_cliente', 'rc');
validarSenha('senha_empresa', 're');

function setupCEP(cepId, numId, endId, cidId, estId, statusId) {
    const cepEl = document.getElementById(cepId);
    if (!cepEl) return;
    cepEl.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '').substring(0, 8);
        v = v.replace(/^(\d{5})(\d)/, '$1-$2');
        e.target.value = v;
        const status = document.getElementById(statusId);
        if (v.replace(/\D/g, '').length === 8) {
            status.textContent = '🔍 Buscando endereço...';
            fetch('https://viacep.com.br/ws/' + v.replace(/\D/g, '') + '/json/')
                .then(r => r.json())
                .then(d => {
                    if (!d.erro) {
                        const num = document.getElementById(numId).value;
                        document.getElementById(endId).value = d.logradouro + (d.bairro ? ', ' + d.bairro : '') + (num ? ', ' + num : '');
                        document.getElementById(cidId).value = d.localidade;
                        document.getElementById(estId).value = d.uf;
                        status.textContent = '✅ Endereço encontrado!';
                        status.style.color = '#22c55e';
                        document.getElementById(numId).focus();
                    } else {
                        status.textContent = '❌ CEP não encontrado.';
                        status.style.color = '#ef4444';
                    }
                })
                .catch(() => {
                    status.textContent = '❌ Erro ao buscar CEP.';
                    status.style.color = '#ef4444';
                });
        } else {
            status.textContent = '';
        }
    });

    document.getElementById(numId)?.addEventListener('input', function() {
        const end = document.getElementById(endId).value;
        if (end) {
            const partes = end.split(', ');
            const semNum = partes.filter(p => isNaN(p.trim())).join(', ');
            document.getElementById(endId).value = semNum + (this.value ? ', ' + this.value : '');
        }
    });
}

setupCEP('cep_cliente', 'numero_cliente', 'endereco_cliente', 'cidade_cliente', 'estado_cliente', 'cep_status_cliente');
setupCEP('cep', 'numero', 'endereco', 'cidade', 'estado', 'cep_status');
</script>
</body>
</html>