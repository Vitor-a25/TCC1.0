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
├── banco.sql
├── auth.css
├── styles.css
├── script.js
├── assets/
│   └── css/
│       └── dashboard.css
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

> ⚠️ **Se o MySQL não iniciar** (porta 3306 ocupada):
> 1. Clique em **Config** na linha do MySQL → abra **my.ini**
> 2. Troque as duas ocorrências de `port=3306` para `port=3307`
> 3. Salve e clique Start novamente
> 4. Se usar porta 3307, siga os passos 3b e 4 abaixo

---

### 2. Colocar o projeto na pasta correta

Copie a pasta `opus/` para dentro de:
```
C:\xampp\htdocs\opus\
```

---

### 3a. Configurar a conexão (porta padrão 3306)

Abra `includes/db.php` e deixe assim:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'plataforma_servicos');
```

### 3b. Configurar a conexão (se usar porta 3307)

Abra `includes/db.php` e deixe assim:
```php
define('DB_HOST', '127.0.0.1:3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'plataforma_servicos');
```

---

### 4. Configurar o phpMyAdmin (somente se usar porta 3307)

Abra o arquivo:
```
C:\xampp\phpMyAdmin\config.inc.php
```

Encontre a linha:
```php
$cfg['Servers'][$i]['host'] = 'localhost';
```

Troque por:
```php
$cfg['Servers'][$i]['host'] = '127.0.0.1:3307';
```

---

### 5. Importar o banco de dados

1. Acesse: **http://localhost/phpmyadmin**
2. Clique em **Novo** no painel esquerdo
3. Digite o nome: `plataforma_servicos` → clique **Criar**
4. Com o banco selecionado, clique na aba **Importar**
5. Clique em **Escolher arquivo** → selecione `banco.sql` da pasta opus
6. Clique em **Executar**

---

### 6. Acessar o sistema

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

> ⚠️ Se as contas demo não logarem, acesse o [phpMyAdmin](http://localhost/phpmyadmin/index.php),
> selecione o banco `plataforma_servicos`, clique na aba **SQL** e execute:
> ```sql
> UPDATE usuario SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
> WHERE email IN ('joao@demo.com','eletroluz@demo.com','fretes@demo.com','techhelp@demo.com');
> ```

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
- Para usar em outro computador, repita todos os passos acima

---

## Tecnologias Utilizadas

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP 8.2 (PDO, Sessions, password_hash)
- **Banco de Dados:** MySQL / MariaDB (InnoDB, UTF8MB4)
- **Servidor:** Apache via XAMPP
- **Fontes:** Google Fonts – Poppins

---

<!-- ============================================================ -->
<!-- ⚠️ REMOVER ESTA SEÇÃO INTEIRA ANTES DE UPAR NO GITHUB ⚠️   -->
<!-- ============================================================ -->

## Como Ligar o Servidor (uso local/apresentação)

### Ligar
1. Abra o **XAMPP Control Panel**
2. Clique **Start** no **Apache** e no **MySQL**
3. Abra o navegador e acesse: `http://localhost/opus/`

### Compartilhar externamente (ngrok)
1. Abra o **Prompt de Comando como Administrador**
2. Execute: `ngrok http 80`
3. Copie o link gerado (ex: `https://xxx.ngrok-free.dev`)
4. Adicione `/opus` no final e compartilhe

### Desligar
1. Feche o ngrok com **Ctrl+C** no Prompt de Comando
2. No XAMPP clique **Stop** no Apache e no MySQL
3. Clique em **Quit** para fechar o XAMPP

<!-- ============================================================ -->
<!-- ⚠️ FIM DA SEÇÃO — REMOVER ANTES DE UPAR NO GITHUB ⚠️       -->
<!-- ============================================================ -->