# 🐛 Guia de Troubleshooting

## ❌ Problemas Comuns e Soluções

### 1. **Conexão com Banco de Dados Falha**

#### Erro: "SQLSTATE[HY000]: General error"

**Causa Provável:**
- Database offline
- Credenciais incorretas
- Host inválido
- Firewall bloqueando conexão

**Solução:**

```bash
# Teste a conexão via terminal
mysql -h terapia.mysql.dbaas.com.br -u terapia -p

# Verifique os parâmetros em config/Database.php
# - Host: terapia.mysql.dbaas.com.br
# - User: terapia  
# - Database: terapia
# - Port: 3306 (padrão)
```

**Debug:**
```php
// Adicionar em config/Database.php para ver erro detalhado
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
```

---

### 2. **Autoload Não Encontra Classes**

#### Erro: "Class not found" ou "Call to undefined function"

**Causa Provável:**
- Namespace incorreto
- Arquivo não criado
- Nome de classe diferente do arquivo

**Solução:**

```php
// Verificar se autoload está funcionando
require_once __DIR__ . '/vendor/autoload.php';

// Testar manualmente
if (file_exists(__DIR__ . '/app/models/User.php')) {
    echo "Arquivo existe";
} else {
    echo "Arquivo NÃO existe - verificar caminho";
}
```

**Padrão de Autoload:**
```
Namespace: App\Models\User
Arquivo: app/models/User.php

Namespace: App\Controllers\AuthController
Arquivo: app/controllers/AuthController.php
```

---

### 3. **Sessão Expirada / Usuário Deslogado**

#### Erro: "Redirecionado para login inesperadamente"

**Causa Provável:**
- SESSION_TIMEOUT expirou (padrão: 1 hora)
- Cookie de sessão deletado
- Navegador em modo incógnito
- PHP session.gc_maxlifetime muito baixo

**Solução:**

```php
// Aumentar timeout em config/Config.php
define('SESSION_TIMEOUT', 86400); // 24 horas

// Verificar cookie_lifetime em php.ini
// session.cookie_lifetime = 0 (até fechar navegador)
// session.gc_maxlifetime = 86400 (24 horas padrão)

// Forçar renovação de sessão
header("Cache-Control: must-revalidate, max-age=0");
header("Pragma: must-revalidate");
```

---

### 4. **Validação CPF/Telefone Rejeitando Valores Válidos**

#### Erro: "CPF inválido" mesmo com CPF correto

**Causa Provável:**
- Formatação diferente esperada
- Dígitos verificadores incorretos
- Espaços/caracteres extras

**Solução:**

```php
// Usar método de formatação antes de validar
$cpf = $this->sanitize($_POST['cpf']); // Remove espaços
$cpf = Validator::formatCPF($cpf);     // Formata
if (Validator::validateCPF($cpf)) {
    // OK
}

// Ou passar sem formatação e deixar a função formatar
if (Validator::validateCPF($cpf_raw)) {
    // Função internamente remove caracteres
}
```

**CPF Teste:**
```
111.444.777-35 - Válido
000.000.000-00 - Inválido
123.456.789-10 - Verificar dígitos
```

---

### 5. **Requisição AJAX Retorna 500 Internal Server Error**

#### Erro: No console aparece erro 500

**Causa Provável:**
- Exceção lançada no PHP
- Erro de syntax
- M_ódulo não carregado
- Query SQL invalida

**Solução:**

```php
// Tentar usar try-catch em controllers
try {
    // seu código aqui
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Ou checar logs do servidor
// $ tail -f /var/log/apache2/error.log
// $ tail -f /var/log/php-fpm.log
```

**Debug AJAX:**
```javascript
// Adicionar em main.js
$.ajax({
    // ...
    error: function(xhr, status, error) {
        console.error("Response:", xhr.responseText);
        console.error("Status:", status);
        console.error("Error:", error);
    }
});
```

---

### 6. **Upload de Arquivo Falha**

#### Erro: "File upload failed"

**Causa Provável:**
- Permissão de pasta (chmod)
- Limite de tamanho
- Tipo de arquivo não permitido
- Pasta não existe

**Solução:**

```bash
# Criar pasta e dar permissão
mkdir -p /var/www/terapia/uploads
chmod 755 /var/www/terapia/uploads
chmod 777 /var/www/terapia/uploads  # Se acima não funcionar

# Verificar permissões
ls -la /var/www/terapia/uploads
```

**Configuração PHP.ini:**
```ini
upload_max_filesize = 20M
post_max_size = 20M
```

**Código de Upload:**
```php
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo "Erro: " . $_FILES['file']['error'];
    // 0 = OK
    // 1 = Arquivo muito grande (MAX_FILE_SIZE)
    // 2 = Arquivo muito grande (upload_max_filesize)
    // 3 = Upload incompleto
    // 4 = Nenhum arquivo
    // 6 = Pasta temporária não existe
    // 7 = Falha ao escrever
    // 8 = Extensão PHP parou o upload
}
```

---

### 7. **Email de Recuperação de Senha Não Chega**

#### Erro: "Email não received"

**Causa Provável:**
- Mailer não configurado
- SMTP bloqueado
- Email em spam
- Domínio não autorizado

**Solução (Quando implementar):**

```php
// Configurar .env
MAIL_HOST=smtp.seuservidor.com
MAIL_PORT=587
MAIL_USER=seu@email.com
MAIL_PASS=sua_senha
MAIL_FROM=noreply@terapia.com

// Usar PHPMailer
$mail = new PHPMailer(true);
$mail->Host = $_ENV['MAIL_HOST'];
$mail->Username = $_ENV['MAIL_USER'];
$mail->Password = $_ENV['MAIL_PASS'];
$mail->setFrom($_ENV['MAIL_FROM']);
```

---

### 8. **Horários Diferentes do Esperado**

#### Erro: "Data/hora aparecem erradas"

**Causa Provável:**
- Timezone PHP diferente
- Timezone do banco diferente
- Timezone do navegador diferente

**Solução:**

```php
// Configurar em config/Config.php
date_default_timezone_set('America/Sao_Paulo');

// Verificar no MySQL
SELECT @@global.time_zone, @@session.time_zone;
SET time_zone = '-03:00'; // UTC-3 para São Paulo

// JavaScript
// Usar Moment.js ou date-fns com locale pt-br
moment.locale('pt-br');
moment().format('DD/MM/YYYY HH:mm');
```

**Banco de dados:**
```sql
-- Verificar timezone atual
SELECT NOW();

-- Converter para outro timezone
SELECT CONVERT_TZ(NOW(), '+00:00', '-03:00'); -- UTC para SP
```

---

### 9. **Gráficos/Charts Não Aparecem**

#### Erro: "Gráficos em branco ou indefinidos"

**Causa Provável:**
- Biblioteca (Chart.js) não carregada
- Dados não passados corretamente
- Elemento canvas/div não existe
- JavaScript erro

**Solução:**

```html
<!-- Verificar se biblioteca está carregada -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Verificar elemento existe -->
<canvas id="myChart"></canvas>

<script>
    // Verificar dados
    console.log(chartData);
    
    // Inicializar
    const ctx = document.getElementById('myChart');
    if (ctx) {
        new Chart(ctx, config);
    } else {
        console.error("Canvas não encontrado");
    }
</script>
```

---

### 10. **Permissão Negada - Não é Admin**

#### Erro: "Sem permissão para acessar"

**Causa Provável:**
- Usuário não é admin
- Session corrompida
- Role não definido corretamente

**Solução:**

```php
// Verificar em Auth.php
public static function requireAdmin() {
    if (!self::isAuthenticated() || self::userRole() !== 'admin') {
        header('Location: /index.php?action=login');
        exit;
    }
}

// Debug - adicionar em dashboard.php
echo "Autenticado: " . (Auth::isAuthenticated() ? 'Sim' : 'Não');
echo "Role: " . Auth::userRole();
echo "User ID: " . Auth::userId();
```

**Banco de dados:**
```sql
-- Verificar role do usuário
SELECT id, email, role FROM users WHERE id = 1;

-- Atualizar para admin se necessário
UPDATE users SET role = 'admin' WHERE id = 1;
```

---

### 11. **CSS/JS Não Carregando**

#### Erro: "Estilos não aplicados, JavaScript não funciona"

**Causa Provável:**
- Caminho incorreto
- Cache do navegador
- Servidor não servindo arquivos estáticos
- Erro no .htaccess

**Solução:**

```html
<!-- Verificar caminhos absolutos -->
<link rel="stylesheet" href="/public/css/dashboard.css">
<script src="/public/js/main.js"></script>

<!-- Ou caminhos relativos (onde você está) -->
<link rel="stylesheet" href="<?php echo __DIR__; ?>/public/css/dashboard.css">

<!-- Limpar cache do navegador -->
Ctrl + Shift + Del (ou Cmd + Shift + Del no Mac)

<!-- No .htaccess, permitir acesso a assets -->
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(public|uploads)/ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
</IfModule>
```

---

### 12. **Form Validation Não Funciona**

#### Erro: "Submeter form sem validar"

**Causa Provável:**
- JavaScript desabilitado
- main.js não carregou
- Elemento form não tem `id="form-create"`
- Validação tem erro syntax

**Solução:**

```html
<!-- Verificar form tem actions -->
<form id="form-create" action="/path/to/controller" method="POST">

<!-- Adicionar validação HTML5 como fallback -->
<input type="email" required>
<input type="tel" pattern="[0-9]{11}" required>

<!-- Debug em main.js -->
$(document).ready(function() {
    console.log("Main.js carregado");
    
    $("#form-create").on("submit", function(e) {
        console.log("Form submit disparado");
        e.preventDefault();
        // continuar
    });
});
```

---

### 13. **Caracteres Especiais Aparecem como ?**

#### Erro: "Ç, ã, é aparecem como ? ou símbolos estranhos"

**Causa Provável:**
- UTF-8 não configurado no MySQL
- Headers UTF-8 não enviado
- HTML não tem charset

**Solução:**

```php
// No topo de cada arquivo PHP
header('Content-Type: text/html; charset=utf-8');

// HTML
<meta charset="UTF-8">

// Database.php ao conectar
$pdo->exec("SET NAMES utf8mb4");
$pdo->exec("SET CHARACTER SET utf8mb4");

// MySQL (criar banco)
CREATE DATABASE terapia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

// Ou alterar banco existente
ALTER DATABASE terapia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE patients CONVERT TO CHARACTER SET utf8mb4;
```

---

### 14. **Pagin ação Não Funciona**

#### Erro: "Quantidade de registros incorreta, navegação quebrada"

**Causa Provável:**
- Offset/limit incorreto
- Total count errado
- Query com WHERE inadequada

**Solução:**

```php
// Em Controller
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$items = $model->find($where, $params, $order, $limit, $offset);
$total = $model->count($where, $params);
$pages = ceil($total / $limit);

// Em View
<ul class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <li><a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
    <?php endfor; ?>
</ul>
```

---

### 15. **PDF Não Gera / Erro ao Exportar**

#### Erro: "Arquivo corrompido ou vazio" ao gerar PDF (Quando usar mPDF)

**Causa Provável:**
- mPDF não instalado
- Permissão pasta temporária
- Memória insuficiente
- HTML/CSS inválido

**Solução (Futura):**

```bash
# Instalar mPDF
composer require mpdf/mpdf

# Verificar permissões
chmod 755 /tmp
chmod 755 /var/tmp
```

```php
// Uso básico
require 'vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output('relatorio.pdf', 'D'); // Download
```

---

## 🔍 Ferramentas de Debug

### 1. **var_dump() e print_r()**
```php
echo "<pre>";
var_dump($variable);
print_r($_SESSION);
echo "</pre>";
```

### 2. **Logging**
```php
// Adicionar em .gitignore: *.log
error_log("Debug message: " . print_r($data, true), 3, "debug.log");
```

### 3. **Chrome DevTools**
- F12 ou Ctrl+Shift+I
- Network - ver requisições
- Console - erros JavaScript
- Elements - inspecionar HTML

### 4. **Xdebug (Avançado)**
```php
// php.ini
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.start_with_request=yes
```

### 5. **PHPMyAdmin**
```
Acessar: phpmyadmin
User: root
Execute queries SQL direto
```

---

## 📋 Checklist de Troubleshooting

- [ ] Verificar logs do servidor (Apache/PHP)
- [ ] Testar conexão com banco (mysql CLI)
- [ ] Verificar permissões de arquivos e pastas
- [ ] Limpar cache (navegador, servidor)
- [ ] Verificar namespaces vs arquivo paths
- [ ] Verificar charset e encoding
- [ ] Verificar timezone
- [ ] Usar console do navegador (F12)
- [ ] Desabilitar JavaScript temporariamente
- [ ] Testar em navegador diferente
- [ ] Verificar .env e .htaccess
- [ ] Ver error_log do PHP
- [ ] Usar curl para testar endpoints
- [ ] Verificar versão PHP (8.0+)
- [ ] Verificar versão MySQL (5.7+ ou 8.0+)

---

**Última atualização:** Março 2026
**Versão:** 1.0
