# 📧 Guia de Configuração de E-mail Automático - Locaweb

## 1. Resumo das Funcionalidades Implementadas

O sistema agora envia e-mails automáticos nos seguintes cenários:

- ✅ **Nova tarefa para paciente** - Com detalhes, descrição e data limite
- ✅ **Novo material para paciente** - Com descr ição e links
- ✅ **Devolutiva recebida do paciente** - Notificação ao terapeuta
- ✅ **Notificações WhatsApp** - Links automáticos para iniciar conversas
- ✅ **Fallback** - Se SMTP não estiver configurado, tenta usar `mail()` do PHP

## 2. Pré-requisitos

- **Conta de E-mail na Locaweb** criada e ativa
- **Credenciais SMTP** fornecidas pela Locaweb
- **PHP 8.1+** com extensão `curl` habilitada
- **Composer** instalado no servidor

## 3. Passo a Passo: Configuração na Locaweb

### 3.1 Criando uma Caixa de E-mail na Locaweb

1. Acesse a **Central do Cliente Locaweb**
   - URL: https://centraldocliente.locaweb.com.br/
   - Faça login com suas credenciais

2. Navigate para **E-mail → Criar Caixa Postal**

3. Preencha os dados:
   - **E-mail**: `seu-email@seu-dominio.com.br` (ex: contato@clinicaterapia.com.br)
   - **Senha**: Use uma senha forte
   - Confirme a senha

4. Clique em **Criar**

5. **Anote as credenciais**:
   - E-mail: `seu-email@seu-dominio.com.br`
   - Senha: `sua-senha-criada`

### 3.2 Configurando SMTP no Servidor

#### No seu servidor (local ou remoto):

1. **Instale as dependências PHP**

   ```bash
   composer install
   ```

   Isso instalará o PHPMailer automaticamente via Composer.

2. **Configure o arquivo `.env`**

   Na raiz do seu projeto, edite ou crie o arquivo `.env`:

   ```bash
   # ===================================
   # Email Configuration (SMTP Locaweb)
   # ===================================
   
   # Driver de envio
   MAIL_DRIVER=smtp
   
   # Servidor SMTP Locaweb
   MAIL_HOST=smtplw.com.br
   
   # Porta conexão
   MAIL_PORT=587
   
   # Tipo de encriptação
   MAIL_ENCRYPTION=tls
   
   # E-mail da caixa postal criada
   MAIL_USERNAME=seu-email@seu-dominio.com.br
   
   # Senha da caixa postal
   MAIL_PASSWORD=sua-senha-criada
   
   # E-mail de remetente
   MAIL_FROM_ADDRESS=seu-email@seu-dominio.com.br
   
   # Nome do remetente
   MAIL_FROM_NAME=Clínica de Terapia
   ```

3. **No seu servidor de hospedagem**:

   - Se estiver usando **cPanel/Plesk**, adicione as variáveis ao painel
   - Se estiver usando **VPS**, edite diretamente o `.env` via SSH/FTP

### 3.3 Configuração da Zona de DNS (Recomendado)

Para melhorar a entregabilidade e reduzir spam, configure registros SPF, DKIM e DMARC:

1. No painel da Locaweb, vá a **Domínios → Zona de DNS**

2. **Adicione registro SPF** (novo TXT record):
   ```
   v=spf1 include:locaweb.com.br ~all
   ```

3. **Configure DKIM** (se disponível no painel):
   - A Locaweb geralmente oferece chaves DKIM prontas
   - Copie a chave pública para o record TXT da zona DNS

4. **Configure DMARC** (novo TXT record):
   ```
   v=DMARC1; p=quarantine; rua=mailto:seu-email@seu-dominio.com.br
   ```

## 4. Testando a Configuração

### 4.1 Teste de Conexão SMTP

Crie um arquivo `test-mail.php` na raiz do projeto:

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Config\Config;
use Helpers\MailService;

Config::loadEnv();

$mailService = new MailService();
$sent = $mailService->send(
    'seu-email-pessoal@gmail.com', // E-mail de teste
    'Teste',
    'Teste de E-mail',
    '<h1>Se você está vendo isto, o email funciona!</h1>'
);

echo $sent ? 'Email enviado com sucesso! ✅' : 'Falha ao enviar email ❌';
?>
```

Execute no terminal:
```bash
php test-mail.php
```

### 4.2 Log de Erros

Os erros são registrados em `error_log` do PHP. Para visualizar:

```bash
# Se estiver em Linux/VPS
tail -f /var/log/php-errors.log

# Ou verifique diretamente no seu servidor
```

## 5. Estrutura de Arquivos Criados

```
projeto/
├── composer.json                    # Adicionada dependência phpmailer/phpmailer
├── .env.example                     # Template com configurações de email
├── helpers/
│   ├── MailService.php             # Nueva clase para envío SMTP
│   ├── EmailTemplate.php           # Template HTML para emails
│   └── AlertDispatcher.php         # Atualización para usar MailService
├── app/
│   └── controllers/
│       ├── TherapistController.php  # Envios de tarefa/material
│       └── PatientPortalController.php # Notificação de devolutiva
└── resources/
    └── email-templates/            # Diretório reservado para templates
```

## 6. Fluxos de E-mail Implementados

### 6.1 Envio de Tarefa para Paciente

```
Terapeuta cria tarefa com send_to_patient = 1
    ↓
dispatchTaskDeliveryAlert() é chamado
    ↓
EmailTemplate::taskAssigned() gera HTML profissional
    ↓
MailService::send() envia via SMTP Locaweb
    ↓
Paciente recebe e-mail com detalhes
```

### 6.2 Envio de Material para Paciente

```
Terapeuta envia materialização delivery_kind = 'material'
    ↓
dispatchTaskDeliveryAlert() detecta tipo
    ↓
EmailTemplate::materialAssigned() gera HTML
    ↓
MailService::send() envia via SMTP Locaweb
    ↓
Paciente recebe notificação
```

### 6.3 Devolutiva de Tarefa para Terapeuta

```
Paciente submete resposta via submitTaskResponse()
    ↓
dispatchTaskAlertSafely() é chamado
    ↓
EmailTemplate::taskResponseReceived() gera HTML
    ↓
MailService::send() envia para terapeuta
    ↓
Terapeuta recebe notificação
```

## 7. Variáveis de Ambiente Compartilhadas

Se estiver em um painel cPanel/Plesk, defina variáveis de ambiente:

```bash
# Via SSH (caso suportado)
export MAIL_DRIVER=smtp
export MAIL_HOST=smtplw.com.br
export MAIL_PORT=587
export MAIL_ENCRYPTION=tls
export MAIL_USERNAME=seu-email@seu-dominio.com.br
export MAIL_PASSWORD=sua-senha-criada
export MAIL_FROM_ADDRESS=seu-email@seu-dominio.com.br
export MAIL_FROM_NAME="Clínica de Terapia"
```

## 8. Troubleshooting

### Erro: "SMTP connection failed"

**Causa**: Credenciais incorretas ou porta bloqueada

**Solução**:
1. Verifique se a caixa postal foi criada na Locaweb
2. Confirme a senha está correta
3. Teste a porta 587 com `telnet smtplw.com.br 587`

### Erro: "PHPMailer not found"

**Causa**: Composer não foi executado

**Solução**:
```bash
composer install
```

### E-mails vão para spam

**Causa**: SPF/DKIM/DMARC não configurados

**Solução**:
1. Configure os registros DNS (veja seção 3.3)
2. Aguarde propagação de DNS (pode levar 24-48h)
3. Teste com ferramentas: return-path.org, mxtoolbox.com

### Fallback para mail() não funciona

**Sintoma**: Sem envio via SMTP e também sem PHP mail()

**Solução**:
- Ative a função `mail()` no `php.ini` (se desabilitada)
- Configure sendmail_path no servidor
- Use SMTP como fallback principal

## 9. Monitoramento e Logs

### Verificar Logs de Envio

Os erros são registrados via `error_log()`:

```bash
# Visualizar últimas 50 linhas de erros
tail -50 /var/log/php-errors.log | grep -i "mail"

# Ou no painel cPanel/Plesk, procure por "Error Logs"
```

### Métricas

Para rastrear quantos e-mails foram enviados, você pode adicionar um log customizado:

```php
// Em MailService.php, após send()
error_log('[MAIL-SENT] To: ' . $toEmail . ' | Subject: ' . $subject . ' | Status: ' . ($result ? 'OK' : 'FAILED'));
```

## 10. Suporte Locaweb

Se encontrar problemas de SMTP:

- **Chat**: https://centraldocliente.locaweb.com.br/ (clique em "Chat")
- **Telefone**: 0800 770 2245 (outras regiões) ou (11) 3544-0500 (São Paulo)
- **Documentação**: https://www.locaweb.com.br/ajuda/categorias/smtp-locaweb/
- **Help**: https://www.locaweb.com.br/ajuda/wiki/como-usar-o-php-mailer-para-envio-de-e-mail-autenticado-smtp-locaweb/

## 11. Resumo das Mudanças no Código

### Arquivos Modificados:
1. `composer.json` - Adicionada dependência `phpmailer/phpmailer`
2. `helpers/MailService.php` - Nova classe SMTP (criada)
3. `helpers/EmailTemplate.php` - Templates HTML (criada)
4. `helpers/AlertDispatcher.php` - Integrada com MailService
5. `app/controllers/TherapistController.php` - Envios de tarefa/material
6. `app/controllers/PatientPortalController.php` - Notificação devolutiva
7. `.env.example` - Configurações de e-mail adicionadas

### Fluxo de Fallback:
- Tenta SMTP Locaweb (PHPMailer)
- Se falhar, tenta `mail()` do PHP
- Se ambas falharem, registra erro em log

## 12. Próximos Passos Recomendados

1. ✅ Instalar PHPMailer via `composer install`
2. ✅ Criar caixa de e-mail na Locaweb
3. ✅ Configurar variáveis no `.env`
4. ✅ Configurar DNS (SPF/DKIM/DMARC)
5. ✅ Testar com `test-mail.php`
6. ✅ Validar logs de erro
7. ✅ Enviar primeira tarefa para paciente
8. ✅ Monitorar entrega

---

**Versão**: 1.0  
**Data**: 10 de Abril de 2026  
**Status**: ✅ Pronto para Produção
