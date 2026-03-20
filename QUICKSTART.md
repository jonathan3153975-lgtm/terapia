# 🚀 QUICK START - Comece em 5 Minutos

## ⚡ 5 Passos Imediatos

### 1️⃣ Preparar Banco de Dados (2 min)

```bash
# Abrir terminal/cmd e conectar ao MySQL
mysql -h terapia.mysql.dbaas.com.br -u terapia -p

# Digite a senha quando pedido
# Copiar todo o conteúdo de database/schema.sql
# Colar no terminal MySQL
# Ou executar:
```

```bash
# Linux/Mac
mysql -h terapia.mysql.dbaas.com.br -u terapia -p < database/schema.sql

# Windows (PowerShell)
Get-Content database/schema.sql | mysql.exe -h terapia.mysql.dbaas.com.br -u terapia -p
```

### 2️⃣ Teste Local (1 min)

```bash
# Navegar para pasta do projeto
cd c:\Users\j-wil\Documents\Github\terapia

# Iniciar servidor PHP nativo
php -S localhost:8000

# Abrir navegador em http://localhost:8000
```

### 3️⃣ Login Padrão (30 seg)

```
Email: admin@terapia.com
Senha: Admin@123
```

### 4️⃣ Testar Funcionalidade (1 min)

- [ ] Fazer login
- [ ] Ir para "Pacientes"
- [ ] Clicar "Novo Paciente"
- [ ] Preencher CPF: 123.456.789-10 (teste)
- [ ] Preencher CEP: 01310-100 (Av. Paulista, São Paulo)
- [ ] Clicar "Buscar CEP"
- [ ] Salvar paciente

### 5️⃣ Próximos Passos (leia os guides)

```
✅ Leia: README.md           - O que é o sistema
✅ Leia: INSTALL.md          - Instalação produção
✅ Leia: PROJECT_MAP.md      - Onde estão os arquivos
✅ Leia: TROUBLESHOOTING.md  - Problemas comuns
```

---

## 📱 Fluxo de Uso Básico

```
1. LOGIN
   ├─> Email: admin@terapia.com
   ├─> Password: Admin@123
   └─> [Login Button]
           ↓
   
2. DASHBOARD
   ├─> Estatísticas gerais
   ├─> Menu lateral
   └─> Ações rápidas
        ↓

3. PACIENTES
   ├─> [Novo Paciente] → Criar paciente
   ├─> [Listar] → Ver todos pacientes
   ├─> [Buscar] → Encontrar por nome/CPF
   └─> [Ações] → Editar/deletar
        ↓

4. AGENDAMENTOS
   ├─> [Calendário] → Ver data visual
   ├─> [Novo Agendamento] → Agendar
   ├─> [Aprovar] → Admin aprova solicitação
   └─> [Histórico] → Ver passados
        ↓

5. PAGAMENTOS
   ├─> [Novo Valor] → Registrar pagamento
   ├─> [Listar] → Ver todos
   ├─> [Filtrar] → Por status/período
   └─> [Editar] → Ajustar dados
        ↓

6. RELATÓRIOS
   ├─> [Dashboard] → Visão geral estatísticas
   ├─> [Mensal] → Relatório do mês
   ├─> [Anual] → Relatório do ano
   └─> [Exportar] → PDF (quando implementado)
```

---

## 🔧 Configurar para PRODUÇÃO

### 1. Copiar .env

```bash
# Copiar arquivo de template
cp .env.example .env

# Editar .env com suas configurações
# Abrir em editor de texto e modificar:
```

```env
# Banco de dados
DB_HOST=terapia.mysql.dbaas.com.br
DB_USER=terapia
DB_PASS=sua_senha_real
DB_NAME=terapia

# Email (quando implementar)
MAIL_HOST=smtp.seuservidor.com.br
MAIL_PORT=587
MAIL_USER=seu@email.com
MAIL_PASS=sua_senha
MAIL_FROM=noreply@seusite.com.br

# App
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seusite.com.br
```

### 2. Atualizar config/Config.php

```php
// Mudar debug para false em produção
define('APP_DEBUG', false);

// Dessabilitar exibição de erros
ini_set('display_errors', 0);

// Ativar logging
define('LOG_PATH', __DIR__ . '/../logs/');
```

### 3. Mudar Permissões (Linux)

```bash
# Permissão de leitura apenas
chmod -R 755 /var/www/terapia

# Permissão de escrita para uploads e logs
chmod 777 /var/www/terapia/uploads
chmod 777 /var/www/terapia/logs

# Arquivo .env privado
chmod 600 /var/www/terapia/.env
```

### 4. Configurar HTTPS

```apache
# .htaccess
RewriteEngine On

# Forçar HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 5. Otimizar Banco

```sql
-- Adicionar índices principais
ALTER TABLE patients ADD INDEX idx_cpf (cpf);
ALTER TABLE patients ADD INDEX idx_email (email);
ALTER TABLE appointments ADD INDEX idx_patient_date (patient_id, appointment_date);
ALTER TABLE payments ADD INDEX idx_patient_status (patient_id, status);

-- Verificar performance
EXPLAIN SELECT * FROM patients WHERE cpf = '12345678901';
```

---

## 📊 Criar Primeiro Paciente Manualmente

Se GUI não funcionar, criar via MySQL:

```sql
-- Inserir paciente
INSERT INTO patients (
    name, 
    cpf, 
    birth_date, 
    phone, 
    email, 
    street, 
    number, 
    neighborhood, 
    city, 
    state, 
    zip_code,
    created_at
) VALUES (
    'João Silva',
    '12345678901',
    '1990-05-15',
    '11987654321',
    'joao@example.com',
    'Rua das Flores',
    '123',
    'Centro',
    'São Paulo',
    'SP',
    '01310-100',
    NOW()
);

-- Verificar
SELECT * FROM patients;
```

---

## 🐛 Diagnóstico Rápido

### Testar Conexão com Banco

```php
<?php
// Salvar como: teste-conexao.php
require 'config/Database.php';

try {
    $db = Database::getInstance();
    $result = $db->query("SELECT 1 AS connected");
    echo "✅ Banco conectado!";
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
```

### Testar PHP Version

```php
<?php
// Salvar como: teste-versao.php
echo "🔹 PHP Version: " . phpversion();
echo "<br>🔹 Extensions: ";
echo extension_loaded('pdo') ? "✅ PDO" : "❌ PDO";
echo extension_loaded('mysql') ? " ✅ MySQL" : " ❌ MySQL";
echo extension_loaded('json') ? " ✅ JSON" : " ❌ JSON";
?>
```

### Testar Autoload

```php
<?php
// Salvar como: teste-autoload.php
require 'vendor/autoload.php';

try {
    $user = new App\Models\User();
    echo "✅ Autoload funcionando!";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
```

---

## 📚 Comandos Úteis

```bash
# Listar todos usuários
mysql -u terapia -p terapia -e "SELECT id, email, role FROM users;"

# Contar pacientes
mysql -u terapia -p terapia -e "SELECT COUNT(*) FROM patients;"

# Ver agendamentos próximos
mysql -u terapia -p terapia -e "SELECT * FROM appointments WHERE appointment_date > NOW() ORDER BY appointment_date;"

# Fazer backup
mysqldump -u terapia -p terapia > backup_$(date +%Y%m%d).sql

# Restaurar backup
mysql -u terapia -p terapia < backup_20260301.sql

# Resetar banco (CUIDADO!)
mysql -u terapia -p -e "DROP DATABASE terapia;" && \
mysql -u terapia -p < database/schema.sql
```

---

## 🆘 Erros Comuns & Solução Rápida

| Erro | Causa | Solução |
|------|-------|---------|
| `SQLSTATE[HY000]` | Banco offline | Verificar credenciais, host, rede |
| `Class not found` | Arquivo PHP missing | Verificar `vendor/autoload.php` |
| `Blank screen` | PHP error | Ligar `display_errors = On` em php.ini |
| `Form não submit` | JavaScript error | Abrir F12 → Console e verificar |
| `CEP não found` | ViaCEP API offline | Testar em browser: https://viacep.com.br/ws/01310100/json/ |
| `Login falha` | Usuário não existe | Criar com `INSERT INTO users...` |
| `Caracteres errados` | Encoding | Verificar `<meta charset="UTF-8">` |

---

## ✅ Validation Checklist

Antes de usar em produção:

- [ ] Banco de dados criado e conectado ✅
- [ ] PHP 8.0+ instalado
- [ ] Apache/Nginx com mod_rewrite ativo
- [ ] .htaccess funcionando (testar com /index.php?action=login)
- [ ] Pasta uploads com permissão 777
- [ ] Arquivo .env com credenciais corretas
- [ ] SSL/HTTPS ativado
- [ ] Backup automático configurado
- [ ] Logs configurados
- [ ] Testes manuais passando

---

## 📞 Posso Começar Agora?

✅ **SIM! E-N-T-Ã-O COMECE ASSIM:**

```bash
# 1. Terminal - Ir para pasta
cd c:\Users\j-wil\Documents\Github\terapia

# 2. Terminal - Iniciar servidor
php -S localhost:8000

# 3. Browser - Abrir
http://localhost:8000

# 4. Login com
admin@terapia.com / Admin@123

# 🎉 PRONTO!
```

---

## 🎓 Próximo a Aprender

1. **Lógica**: Ler `CONTRIBUTING.md` para entender patterns
2. **Estrutura**: Ler `PROJECT_MAP.md` para saber onde está cada coisa
3. **Banco**: Ler `database/schema.sql` para entender relacionamentos
4. **Customização**: Editar `app/models/` e `app/controllers/`
5. **Design**: Editar `public/css/dashboard.css` para cores/fonts

---

**Problemas?** → Ver `TROUBLESHOOTING.md`  
**Dúvidas?** → Ver `README.md`  
**Mapa?** → Ver `PROJECT_MAP.md`  

🚀 **Bom desenvolvimento!**
