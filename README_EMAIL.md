# 📧 Sistema de E-mail Automático - Documentação Completa

## 🎯 Visão Geral

Sistema completo de envio automático de e-mails implementado para plataforma de terapia, integrado com **SMTP Locaweb**. 

Envia notificações profissionais em HTML quando:
- ✅ Terapeuta cria tarefa para paciente
- ✅ Terapeuta compartilha material
- ✅ Paciente submete resposta de tarefa
- ✅ Apoio via WhatsApp (links automáticos)

## 📚 Documentação

| Arquivo | Propósito | Público |
|---------|-----------|---------|
| **QUICK_START_EMAIL.md** | Configuração em 5 minutos | Desenvolvedores |
| **GUIA_EMAIL_LOCAWEB.md** | Guia completo e detalhado | Administradores |
| **LOCAWEB_TECNICO.md** | Referência técnica | Técnicos |
| **EMAIL_SETUP_SUMMARY.md** | Resumo executivo | Gestores |
| **CHECKLIST_IMPLEMENTACAO.md** | Passo a passo verificável | Todos |
| **README_EMAIL.md** | Este arquivo | Referência |

### 👉 Comece por aqui:
1. Se tem pouco tempo → **QUICK_START_EMAIL.md**
2. Se precisa configurar → **GUIA_EMAIL_LOCAWEB.md**
3. Se tem problemas técnicos → **LOCAWEB_TECNICO.md**

## 🏗️ Arquitetura

```
┌─────────────────────────────────────────────────────────┐
│                   Eventos do Sistema                     │
│        (Nova Tarefa, Material, Devolutiva)              │
└────────────────┬────────────────────────────────────────┘
                 │
        ┌────────▼─────────┐
        │ Controllers      │
        │ - Therapist      │
        │ - PatientPortal  │
        └────────┬─────────┘
                 │
      ┌──────────▼──────────┐
      │  EmailTemplate      │
      │  Gera HTML prof.    │
      └──────────┬──────────┘
                 │
        ┌────────▼────────┐
        │  MailService    │
        │  PHPMailer SMTP │
        └────────┬────────┘
                 │
    ┌────────────▼────────────┐
    │  smtplw.com.br:587      │
    │  Locaweb SMTP Server    │
    └────────────┬────────────┘
                 │
    ┌────────────▼────────────┐
    │  📧 E-mail Entregue     │
    │  (Inbox do Recipient)   │
    └─────────────────────────┘
```

## 📦 Componentes Implementados

### 1. **MailService** (`helpers/MailService.php`)

Classe responsável pelo envio via SMTP com fallback para `mail()`.

```php
// Uso básico
$mail = new MailService();
$sent = $mail->send(
    'recipient@email.com',
    'Nome Recipiente',
    'Assunto',
    '<h1>HTML Email</h1>'
);
```

**Características**:
- Configuração automática Locaweb
- PHPMailer (mais confiável que `mail()`)
- Fallback inteligente
- Tratamento de exceções

### 2. **EmailTemplate** (`helpers/EmailTemplate.php`)

Templates HTML profissionais para diferentes eventos.

```php
// Templates disponíveis
EmailTemplate::taskAssigned($name, $title, $desc, $dueDate)
EmailTemplate::materialAssigned($name, $title, $desc)
EmailTemplate::taskResponseReceived($therapist, $patient, $taskTitle)
EmailTemplate::welcomePatient($name, $loginUrl)
```

**Características**:
- HTML responsivo
- Branding profissional
- Links automáticos
- Sem dependências externas

### 3. **AlertDispatcher** (`helpers/AlertDispatcher.php`) - Atualizado

Integrado com MailService para envios de e-mail + WhatsApp.

```php
// Dispatcha alertas em múltiplos canais
$report = AlertDispatcher::dispatch(
    ['email', 'whatsapp'],
    'user@email.com',
    '11999999999',
    'Assunto',
    'Mensagem'
);

// Sumariz result
$summary = AlertDispatcher::summarize($report);
```

## 🔄 Fluxos de Automação

### Fluxo 1: Envio de Tarefa

```
Terapeuta clica "Criar Tarefa"
    ↓
Marca "Enviar ao Paciente" = SIM
    ↓
TherapistController::storePatientTask()
    ↓
dispatchTaskDeliveryAlert()
    ↓
EmailTemplate::taskAssigned() → HTML
    ↓
MailService::send() → SMTP Locaweb
    ↓
📧 Paciente recebe notificação
```

### Fluxo 2: Compartilhamento de Material

```
Terapeuta cria material
    ↓
Marca "Enviar ao Paciente" + "Tipo: Material"
    ↓
dispatchTaskDeliveryAlert(deliveryKind='material')
    ↓
EmailTemplate::materialAssigned() → HTML
    ↓
MailService::send() → SMTP Locaweb
    ↓
📧 Paciente recebe material
```

### Fluxo 3: Devolutiva de Tarefa

```
Paciente acessa: /patient.php?action=task-respond
    ↓
Preenche resposta + anexos
    ↓
Clica "Enviar Resposta"
    ↓
PatientPortalController::submitTaskResponse()
    ↓
dispatchTaskAlertSafely()
    ↓
EmailTemplate::taskResponseReceived() → HTML
    ↓
MailService::send() → Terapeuta
    ↓
👥 Terapeuta notificado
```

## ⚙️ Configuração

### Variáveis `.env` Necessárias

```env
# Driver
MAIL_DRIVER=smtp

# SMTP Locaweb
MAIL_HOST=smtplw.com.br
MAIL_PORT=587
MAIL_ENCRYPTION=tls

# Credenciais
MAIL_USERNAME=seu-email@seu-dominio.com.br
MAIL_PASSWORD=sua-senha-locaweb

# Remetente
MAIL_FROM_ADDRESS=seu-email@seu-dominio.com.br
MAIL_FROM_NAME=Sua Clínica
```

### Criando Caixa de E-mail na Locaweb

1. Acesse https://centraldocliente.locaweb.com.br/
2. Email → Criar Caixa Postal
3. Email: `seu-email@seu-dominio.com.br`
4. Senha: defina uma senha forte
5. Clique "Criar"

### Testando a Configuração

```bash
# Criar teste
echo '<?php
require "vendor/autoload.php";
use Config\Config; use Helpers\MailService;
Config::loadEnv();
$m = new MailService();
echo $m->send("seu-email@gmail.com","T","T","<h1>OK</h1>") ? "✅" : "❌";
?>' > test-mail.php

# Executar
php test-mail.php
```

## 🛡️ Segurança

✅ **Implementadas**:
- Triple-layer sanitização HTML
- Tratamento de exceções em todos os pontos
- Logs de erro sem credenciais
- Validação de e-mail antes de enviar
- Fallback seguro

⚠️ **Cuidados**:
- Manter `.env` fora do Git (já em `.gitignore`?)
- Usar HTTPS em todos os links
- Monitorar logs para falhas repetidas
- Testar SPF/DKIM antes de produção

## 📊 Performance

- **Tempo de envio**: ~1-5 segundos via SMTP
- **Failover**: Automático para `mail()` se SMTP falhar
- **Timeout**: 30 segundos por e-mail
- **Batches**: Suporte para múltiplos destinatários

## 🚀 Features

- ✅ SMTP Locaweb integrado
- ✅ Templates HTML profissionais responsivos
- ✅ WhatsApp links automáticos
- ✅ Fallback para PHP mail()
- ✅ Logs detalhados
- ✅ Tratamento de erros robusto
- ✅ Suporte a múltiplos canais (email + whatsapp)
- ✅ Sanitização HTML avançada

## 📈 Monitoramento

### Verificar Envios

```bash
# Logs de erro
grep -i "mail" /var/log/php-errors.log

# Últimas 50 linhas
tail -50 /var/log/php-errors.log | grep -i mail
```

### Métricas

Adicione logging customizado em `MailService.php`:

```php
error_log('[MAIL-SENT] To: ' . $toEmail . ' | Status: ' . ($sent ? 'OK' : 'FAIL'));
```

## 🔗 Integrações

### Com Existing AlertDispatcher

```php
// Antes (apenas texto plano)
AlertDispatcher::dispatch(['email'], $email, $phone, 'Assunto', 'Texto');

// Agora (com HTML)
$html = EmailTemplate::taskAssigned(...);
$mail = new MailService();
$mail->send($email, 'Nome', 'Assunto', $html);
```

### Com Controllers

```php
// TherapistController
if ($deliveryKind === 'material') {
    $html = EmailTemplate::materialAssigned($name, $title, $desc);
} else {
    $html = EmailTemplate::taskAssigned($name, $title, $desc, $date);
}
$mailService = new MailService();
$mailService->send($email, $name, $subject, $html);
```

## 📞 FAQ

**P: E se SMTP não estiver configurado?**  
R: Sistema tenta automaticamente usar `mail()` do PHP.

**P: E se o e-mail for inválido?**  
R: Validação FILTER_VALIDATE_EMAIL previne envios inúteis.

**P: Como rastrear se foi enviado?**  
R: Verifique logs com `grep "mail" error_log` ou consulte webmail Locaweb.

**P: Por que vai para SPAM?**  
R: Configure SPF/DKIM no DNS (veja GUIA_EMAIL_LOCAWEB.md).

**P: Suporta anexos?**  
R: PHPMailer suporta. Extra coding seria necessário. Foco atual em templates HTML.

**P: Posso customizar os templates?**  
R: Sim! Edite `helpers/EmailTemplate.php` e modifique o HTML.

## 🐛 Troubleshooting

### "Connection refused"
```bash
# Teste conexão
telnet smtplw.com.br 587
```
Se não conectar, verifique firewall ou tente porta 465.

### "Authentication failed"
Verifique:
- E-mail correto? (MAIL_USERNAME)
- Senha correcta? (MAIL_PASSWORD)
- Caixa postal criada? (Central Locaweb)

### "Email não chega"
1. Verifique Spam/Junk
2. Configure SPF/DKIM
3. Verifique logs
4. Contacte Locaweb

## 📄 Arquivos Modificados

```
NOVO:
✅ helpers/MailService.php
✅ helpers/EmailTemplate.php
✅ GUIA_EMAIL_LOCAWEB.md
✅ QUICK_START_EMAIL.md
✅ LOCAWEB_TECNICO.md
✅ EMAIL_SETUP_SUMMARY.md
✅ CHECKLIST_IMPLEMENTACAO.md

MODIFICADO:
✅ composer.json
✅ .env.example
✅ helpers/AlertDispatcher.php
✅ app/controllers/TherapistController.php
✅ app/controllers/PatientPortalController.php
```

## ✨ Roadmap Futuro

- [ ] Suporte a templates multilíngue
- [ ] Análise de entregabilidade
- [ ] Dashboard de monitoramento de emails
- [ ] Retry automático de falhas
- [ ] Suporte a anexos em e-mail
- [ ] Templates customizáveis via painel admin

## 📞 Suporte

- **Locaweb**: https://centraldocliente.locaweb.com.br/ (Chat 24/7)
- **Documentação**: Arquivos `.md` neste diretório
- **Logs**: `/var/log/php-errors.log`

---

**Versão**: 1.0  
**Status**: ✅ Pronto para Produção  
**Última Atualização**: 10 de Abril de 2026  
**Desenvolvido por**: Sistema de Terapia
