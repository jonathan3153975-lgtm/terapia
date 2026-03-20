# 🚀 Guia Rápido de Instalação - Sistema Terapia

## ⚡ Instalação em 5 Minutos

### Passo 1: Preparar o Banco de Dados

```bash
# Acesse seu MySQL via linha de comando
mysql -h terapia.mysql.dbaas.com.br -u terapia -p

# Digite a senha: Jonathan315@@

# Dentro do MySQL, execute:
CREATE DATABASE IF NOT EXISTS terapia;
USE terapia;
```

### Passo 2: Executar Script SQL

```bash
# No terminal, no diretório raiz do projeto:
mysql -h terapia.mysql.dbaas.com.br -u terapia -p terapia < database/schema.sql
```

### Passo 3: Configurar Credenciais (Opcional)

A aplicação já vem configurada com as credenciais fornecidas. Se precisar mudar:

**Arquivo:** `config/Database.php`

```php
private const HOST = 'terapia.mysql.dbaas.com.br';
private const DATABASE = 'terapia';
private const USER = 'terapia';
private const PASSWORD = 'Jonathan315@@';
```

### Passo 4: Configurar URL (Importante!)

**Arquivo:** `config/Config.php`

```php
public const APP_URL = 'http://seu-dominio.com/terapia';
```

Se estiver local:
```php
public const APP_URL = 'http://localhost/terapia';
```

### Passo 5: Acessar a Aplicação

```
🌐 http://localhost/terapia/index.php
```

ou

```
🌐 http://seu-dominio.com/terapia/index.php
```

## 🔑 Credenciais de Acesso

- **Email:** `admin@terapia.com`
- **Senha:** `Admin@123`

> ⚠️ **IMPORTANTE:** Altere a senha padrão após o primeiro acesso!

## 📋 Checklist Pós-Instalação

- [ ] Banco de dados criado
- [ ] Script SQL executado
- [ ] Tabelas criadas com sucesso
- [ ] Credenciais configuradas
- [ ] URL configurada corretamente
- [ ] Sistema acessível via navegador
- [ ] Login funciona com credenciais padrão
- [ ] Senha padrão alterada

## 🐛 Troubleshooting

### Erro: "Erro de conexão"
- Verifique as credenciais do banco em `config/Database.php`
- Confirme que o banco `terapia` foi criado
- Verifique se o host está correto

### Erro: "Página não encontrada (404)"
- Confira o valor de `APP_URL` em `config/Config.php`
- Verifique se o arquivo `dashboard.php` existe na raiz
- Confirme que o servidor está rodando

### Erro: "Comando MySQL não encontrado"
- Se estiver no Windows, adicione MySQL ao PATH
- Use o caminho completo: `C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe`

### Erro: "Permission denied"
- Se for arquivo .sql: `chmod 644 database/schema.sql`
- Se for pasta uploads: `chmod 755 uploads/`

## 📦 Requisitos do Sistema

- PHP 8.0 ou superior
- MySQL 8.0 ou superior
- Apache ou Nginx com mod_rewrite
- Navegador moderno (Chrome, Firefox, Safari, Edge)

## 🔧 Verificar Versões

```bash
# Verificar versão PHP
php --version

# Verificar MySQL
mysql --version
```

## 💡 Dicas

1. **Mudar senha admin:**
   - Faça login com `admin@terapia.com` / `Admin@123`
   - Vá em Configurações (quando implementado)
   - Altere a senha

2. **Adicionar mais usuários:**
   - No painel admin, vá em Usuários
   - Clique em "Novo Usuário"
   - Preencha os dados

3. **Backup do banco:**
   ```bash
   mysqldump -h terapia.mysql.dbaas.com.br -u terapia -p terapia > backup.sql
   ```

4. **Restaurar backup:**
   ```bash
   mysql -h terapia.mysql.dbaas.com.br -u terapia -p terapia < backup.sql
   ```

## 📞 Suporte

Se encontrar problemas:

1. Consulte **README.md** para documentação completa
2. Veja **FEATURES.md** para lista de funcionalidades
3. Abra uma issue no GitHub (se aplicável)
4. Verifique os logs do servidor/PHP

## 🎉 Pronto!

Você agora tem um sistema completo de administração para consultório de terapias!

**Comece a:**
- ✅ Adicionar pacientes
- ✅ Fazer agendamentos
- ✅ Registrar atendimentos
- ✅ Gerenciar pagamentos
- ✅ Gerar relatórios

---

**Desenvolvimento profissional com ❤️**
