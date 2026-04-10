# ⚡ QUICK START - E-mail em 5 Minutos

## 1️⃣ Instalar PHPMailer
```bash
composer install
```

## 2️⃣ Criar E-mail na Locaweb

1. Acesse: https://centraldocliente.locaweb.com.br/
2. Email → Criar Caixa Postal
3. Exemplo: `contato@clinicaterapia.com.br`
4. Defina senha: `SenhaForte123!`
5. Salve as credenciais

## 3️⃣ Configurar `.env` (5 linhas importantes)

Crie/edite arquivo `.env` na raiz:

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtplw.com.br
MAIL_PORT=587
MAIL_USERNAME=contato@clinicaterapia.com.br
MAIL_PASSWORD=SenhaForte123!
MAIL_FROM_ADDRESS=contato@clinicaterapia.com.br
MAIL_FROM_NAME=Terapia Sistêmica
```

## 4️⃣ Testar

Crie `test.php`:

```php
<?php
require 'vendor/autoload.php';
use Config\Config;
use Helpers\MailService;
Config::loadEnv();
$m = new MailService();
echo $m->send('seu-email@gmail.com','Teste','Teste','Funciona!') ? '✅' : '❌';
?>
```

```bash
php test.php
```

## 5️⃣ Usar

- **Criar tarefa** → Marque "Enviar ao Paciente" ✅
- **Paciente responde** → Você recebe notificação ✅
- **Pronto!** 🎉

---

📖 Para configurações avançadas: ver `GUIA_EMAIL_LOCAWEB.md`
