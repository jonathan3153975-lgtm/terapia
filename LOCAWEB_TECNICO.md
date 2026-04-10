# 🔧 Configurações Técnicas Locaweb - Referência Rápida

## SMTP Locaweb - Dados de Conexão

| Configuração | Valor |
|---|---|
| **Host SMTP** | `smtplw.com.br` |
| **Porta** | `587` (recomendado) ou `465` (SSL) |
| **Autenticação SSL/TLS** | Sim (recomendado: TLS) |
| **Usuário** | Seu email completo: `seu-email@seu-dominio.com.br` |
| **Senha** | Senha da caixa postal criada |

## Comparativa: Porta 587 vs 465

| Aspecto | Porta 587 (TLS) | Porta 465 (SSL) |
|---|---|---|
| **Segurança** | Boa (TLS) | Muito Boa (SSL) |
| **Compatibilidade** | Universal | Alguns clientes antigos |
| **Velocidade** | Ligeiramente mais rápida | Ligeiramente mais lenta |
| **Recomendação** | ✅ Usar esta | ❌ Se porta 587 não funcionar |

## Configuração no `.env`

### Opção 1: TLS (Recomendado - Porta 587)
```env
MAIL_HOST=smtplw.com.br
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_SMTPAuth=true
```

### Opção 2: SSL (Alternativa - Porta 465)
```env
MAIL_HOST=smtplw.com.br
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_SMTPAuth=true
```

## Limites da Locaweb

| Limite | Valor |
|---|---|
| **E-mails por hora** | ~1000 emails/hora (depende do plano) |
| **Tamanho máx. anexo** | 25 MB por e-mail |
| **Tamanho máx. mensagem** | 50 MB |
| **Caixas por domínio** | Ilimitadas (depende do plano) |
| **Retenção de mensal** | 90 dias (padrão) |

## DNS Records (Recomendado Adicionar)

### SPF Record (TXT)
```
v=spf1 include:locaweb.com.br ~all
```

### DKIM (se disponível)
Locaweb fornece chave DKIM → Adicionar como TXT record

### DMARC (TXT)
```
v=DMARC1; p=quarantine; rua=mailto:seu-email@seu-dominio.com.br; ruf=mailto:seu-email@seu-dominio.com.br
```

## Troubleshooting Rápido

### "Connection timed out"
→ Porta bloqueada pelo firewall  
→ Tente porta 465 no lugar de 587

### "Authentication failed"
→ Email ou senha incorretos  
→ Verifique maiúsculas/minúsculas/espaços

### "Relay access denied"
→ Usuário não é o remetente (usar mesmo email)  
→ Configure MAIL_FROM_ADDRESS = MAIL_USERNAME

### "TLS negotiation failed"
→ Servidor/firewall não suporta TLS  
→ Tente SSL (porta 465)

### E-mails vão para SPAM
→ Configure SPF/DKIM/DMARC  
→ Espere 24-48h de propagação DNS  
→ Teste com https://www.mail-tester.com/

## Tools Úteis para Testes

| Ferramenta | URL | Uso |
|---|---|---|
| **MXToolbox** | https://mxtoolbox.com/ | Check SPF/DKIM/mx records |
| **Return-Path** | https://www.return-path.org/test | Testar entregabilidade |
| **Mail-Tester** | https://www.mail-tester.com/ | Análise completa de email |
| **55up** | https://check-mail.org/ | Validar DNS records |

## Documentação Locaweb Oficial

- **SMTP Geral**: https://www.locaweb.com.br/ajuda/categorias/smtp-locaweb/
- **PHP Mailer**: https://www.locaweb.com.br/ajuda/wiki/como-usar-o-php-mailer-para-envio-de-e-mail-autenticado-smtp-locaweb/
- **Configurar Outlook**: https://ajuda.locaweb.com.br/wiki/configuracao-de-outlook-e-mail-locaweb/
- **Boas Práticas**: https://www.locaweb.com.br/ajuda/wiki/veja-as-boas-praticas-de-envio-smtp-locaweb/

## Contato Suporte Locaweb

| Canal | Contato | Disponibilidade |
|---|---|---|
| **Chat** | Painel da Locaweb | 24/7 |
| **Telefone SP** | (11) 3544-0500 | 24/7 |
| **Telefone BR** | 0800 770 2245 | 24/7 |
| **Email** | suporte@locaweb.com.br | Comercial |

## Rotinas de Manutenção Recomendadas

### Semanal
- [ ] Verificar erro_log para falhas de envio
- [ ] Testar uma tarefa até o fim

### Mensal
- [ ] Revisar relatório de entrega de emails
- [ ] Verifi Car se há emails em quarentena

### Trimestral
- [ ] Atualizar registros SPF se necessário
- [ ] Revisar limite de envios (aumentar se necessário)

## Exemplo Completo: Fluxo de Envio

```
1. Terapeuta acessa: dashboard.php?action=patients-history&id=5
2. Cria tarefa "Exercício de relaxamento"
3. Marca "Enviar ao Paciente"
4. Clica "Salvar Tarefa"
   ↓
5. TherapistController::storePatientTask() executado
6. Tarefa inserida no BD
7. dispatchTaskDeliveryAlert() chamado
8. EmailTemplate::taskAssigned() gera HTML
9. MailService::send() conecta via SMTP
   ↓
10. Locaweb: smtplw.com.br:587 receita
11. TLS negociado
12. Autenticação: seu-email@seu-dominio.com.br
13. Email enviado pro paciente@email.com
    ↓
14. Paciente recebe em 1-5 segundos
15. Pode clicar em "Ir para Tarefas"
16. Vê tarefa no portal
```

## Código de Teste Completo

```php
<?php
// test-locaweb-smtp.php
require __DIR__ . '/vendor/autoload.php';

use Helpers\MailService;
use Config\Config;

Config::loadEnv();

// Testa conexão
$mail = new MailService();

// Seu email de teste
$testEmail = 'seu-email@gmail.com';
$testHtml = '<h1>Teste Locaweb</h1><p>Se você está vendo isto, SMTP funciona!</p>';

echo "Testando SMTP Locaweb...\n";
echo "Host: " . Config::get('MAIL_HOST', 'não configurado') . "\n";
echo "Porta: " . Config::get('MAIL_PORT', 'não configurada') . "\n";
echo "De: " . Config::get('MAIL_FROM_ADDRESS', 'não configurado') . "\n";
echo "Para: " . $testEmail . "\n\n";

$result = $mail->send($testEmail, 'Teste', 'Teste Locaweb SMTP', $testHtml);

echo $result 
    ? "✅ EMAIL ENVIADO COM SUCESSO!\n" 
    : "❌ Falha ao enviar. Verifique .env e logs.\n";

// Verifica .env
if (!file_exists('.env')) {
    echo "⚠️ Arquivo .env não encontrado!\n";
} else {
    echo "✅ Arquivo .env encontrado\n";
}
?>
```

Execute:
```bash
php test-locaweb-smtp.php
```

---

**Última atualização**: 10 de Abril de 2026  
**Versão**: 1.0-stable
