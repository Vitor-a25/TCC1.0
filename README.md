# OPUS — Plataforma de Serviços

## Estrutura de Arquivos

```
opus/
├── index.php
├── login.php
├── cadastro.php
├── logout.php
├── usuario.php
├── empresa.php
├── admin.php
├── backup.php
├── banco.sql
├── auth.css
├── styles.css
├── script.js
├── assets/
│   └── css/
│       └── dashboard.css
├── backups/
│   └── (backups automáticos diários)
└── includes/
    ├── db.php
    └── auth.php
```

---

## Instalação passo a passo

### 1. Instalar o XAMPP

1. Baixe o XAMPP em: https://www.apachefriends.org
2. Instale a versão **8.2.12 / PHP 8.2.12**
3. Abra o **XAMPP Control Panel**
4. Clique em **Start** no **Apache** e no **MySQL**
   - Ambos devem ficar com fundo **verde**

---

### 2. Colocar o projeto na pasta correta

Copie a pasta `opus/` para dentro de:
```
C:\xampp\htdocs\opus\
```

---

### 3. Configurar a conexão

Abra `includes/db.php` e deixe assim:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'plataforma_servicos');
```

---

### 4. Importar o banco de dados

1. Acesse: **http://localhost/phpmyadmin**
2. Clique na aba **Importar** na tela inicial
3. Clique em **Escolher arquivo** → selecione `banco.sql` da pasta opus
4. Clique em **Executar**

---

### 5. Acessar o sistema

Abra o navegador e acesse:
```
http://localhost/opus/
```

---

## Contas de Acesso Demo

| Perfil | E-mail | Senha |
|--------|--------|-------|
| Administrador | admin@opus.com | password |
| Cliente | joao@demo.com | password |
| Empresa (EletroLuz) | eletroluz@demo.com | password |
| Empresa (Fretes) | fretes@demo.com | password |
| Empresa (TechHelp) | techhelp@demo.com | password |

---

## Fluxo de Uso

1. Visitante acessa `index.php` e visualiza o catálogo público
2. Cria conta em `cadastro.php` escolhendo entre Cliente ou Empresa
3. Faz login em `login.php` — o sistema redireciona automaticamente pelo perfil
4. **Cliente** busca serviços → visualiza empresa → solicita atendimento → avalia
5. **Empresa** cadastra perfil → cadastra serviços → responde solicitações
6. **Admin** gerencia todos os dados da plataforma

---

## Observações importantes

- O XAMPP precisa estar aberto e com **Apache + MySQL rodando** sempre que for usar o sistema
- Os dados ficam salvos no banco mesmo fechando o navegador
- O sistema realiza **backup automático diário** do banco de dados na pasta `backups/`, mantendo os últimos 7 dias
- Para usar em outro computador, repita todos os passos acima

---

## Tecnologias Utilizadas

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP 8.2 (PDO, Sessions, password_hash)
- **Banco de Dados:** MySQL / MariaDB (InnoDB, UTF8MB4)
- **Servidor:** Apache via XAMPP
- **Fontes:** Google Fonts – Poppins

---

## Solução de Problemas

### MySQL não inicia no XAMPP
O MySQL pode não iniciar se outro processo estiver usando a porta 3306 (como o MySQL nativo do Windows).

1. Abra o **Prompt de Comando como Administrador**
2. Execute:
   ```
   netstat -ano | findstr :3306
   ```
3. Anote o PID que aparecer e execute:
   ```
   taskkill /PID xxxx /F
   ```
4. Volte ao XAMPP e clique **Start** no MySQL

---

### Página não abre no navegador
- Verifique se o **Apache** está rodando (fundo verde no XAMPP)
- Confirme que a pasta do projeto está em `C:\xampp\htdocs\opus\`
- Tente acessar: `http://127.0.0.1/opus/`

---

### Erro de conexão com o banco
- Verifique se o **MySQL** está rodando (fundo verde no XAMPP)
- Confirme as configurações em `includes/db.php`
- Verifique se o banco `plataforma_servicos` foi criado no phpMyAdmin

---

### Contas demo não conseguem logar
Acesse o [phpMyAdmin](http://localhost/phpmyadmin/index.php), selecione o banco `plataforma_servicos`, clique na aba **SQL** e execute:

```sql
UPDATE usuario SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email IN ('joao@demo.com','eletroluz@demo.com','fretes@demo.com','techhelp@demo.com','admin@opus.com');
```

Isso redefine todas as senhas para **password**.
