<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) {
    $tipo = getTipo();
    redirect($tipo === 'admin' ? 'admin.php' : ($tipo === 'empresa' ? 'empresa.php' : 'usuario.php'));
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha']       ?? '';

    if (!$email || !$senha) {
        $erro = 'Preencha todos os campos.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id, nome, senha, tipo FROM usuario WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nome']       = $user['nome'];
            $_SESSION['tipo']       = $user['tipo'];

            if ($user['tipo'] === 'empresa') {
                $s2 = $db->prepare('SELECT id FROM empresa WHERE usuario_id = ?');
                $s2->execute([$user['id']]);
                $emp = $s2->fetch();
                $_SESSION['empresa_id'] = $emp['id'] ?? null;
            }

            redirect($user['tipo'] === 'admin' ? 'admin.php' : ($user['tipo'] === 'empresa' ? 'empresa.php' : 'usuario.php'));
        } else {
            $erro = 'E-mail ou senha incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | OPUS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="auth.css">
</head>
<body>
<main class="auth">
  <section class="auth-card">
    <a href="index.php" class="brand">OPUS</a>
    <h1>Fazer login</h1>
    <p>Acesse sua conta para buscar serviços, solicitar atendimento ou gerenciar sua empresa.</p>

    <?php if ($erro): ?>
      <div style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#f87171;padding:12px 16px;border-radius:12px;margin-bottom:16px;font-size:14px;text-align:center;"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>E-mail
        <input type="email" name="email" placeholder="Digite seu e-mail" required>
      </label>
      <label>Senha
        <div style="position:relative">
  <input type="password" name="senha" id="senha_login" placeholder="Digite sua senha" required style="padding-right:48px">
  <button type="button" onclick="toggleSenha('senha_login', this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:rgba(255,255,255,.5);cursor:pointer;font-size:18px;padding:4px">👁️</button>
</div>
      </label>
      <button type="submit">Entrar</button>
    </form>

    <span>Não possui conta? <a href="cadastro.php">Criar conta</a></span>
  </section>
</main>
<script>
function toggleSenha(id, btn) {
    const el = document.getElementById(id);
    if (el.type === 'password') {
        el.type = 'text';
        btn.textContent = '🙈';
    } else {
        el.type = 'password';
        btn.textContent = '👁️';
    }
}
</script>
</body>
</html>
