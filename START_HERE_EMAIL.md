# 🚀 START HERE - E-mail Automático

## ⏱️ Tempo Total: ~15 minutos

Você está aqui porque foi implementado um **sistema completo de e-mail automático** para o seu portal de terapia.

## O Que Funciona Agora

✅ **Terapeuta cria tarefa** → Paciente recebe e-mail automático  
✅ **Terapeuta compartilha material** → Paciente notificado  
✅ **Paciente responde tarefa** → Terapeuta recebe notificação  
✅ **Links WhatsApp automáticos** → Integração com alertas  

## 3️⃣ Passos Simples

### PASSO 1: Ativar (2 min)
```bash
cd /seu-diretório-do-projeto
composer install
```

### PASSO 2: Configurar (5 min)
**Na raiz do projeto, crie/edite `.env`:**

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtplw.com.br
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=seu-email@seu-dominio.com.br
MAIL_PASSWORD=sua-senha-locaweb
MAIL_FROM_ADDRESS=seu-email@seu-dominio.com.br
MAIL_FROM_NAME=Seu Nome ou Clínica
```

### PASSO 3: Testar (2 min)
```bash
php -r "require 'vendor/autoload.php'; use Config\Config; use Helpers\MailService; Config::loadEnv(); echo (new MailService())->send('seu-email@gmail.com','Teste','Teste','<h1>OK!</h1>') ? '✅ FUNCIONA' : '❌ ERRO';"
```

---

## 📚 Documentação por Perfil

### 👨‍💻 Para Desenvolvedores
1. Leia: **QUICK_START_EMAIL.md** (5 min)
2. Testar no seu ambiente local
3. Revisar: **README_EMAIL.md** para arquitetura

### 👨‍💼 Para Administradores
1. Leia: **GUIA_EMAIL_LOCAWEB.md** (20 min)
2. Criar caixa de e-mail na Locaweb
3. Configurar `.env`
4. Usar: **CHECKLIST_IMPLEMENTACAO.md** para validar

### 🔧 Para Técnicos
1. Consulte: **LOCAWEB_TECNICO.md**
2. Configure DNS (SPF, DKIM, DMARC)
3. Monitore logs
4. Faça troubleshooting conforme necesário

---

## 🎯 Próximos Passos Reais

1. **Hoje**
   - [ ] Execute `composer install`
   - [ ] Configure `.env`
   - [ ] Teste envio local

2. **Este Mês**
   - [ ] Crie caixa de e-mail na Locaweb
   - [ ] Valide SMTP em produção
   - [ ] Configure DNS (SPF/DKIM)
   - [ ] Treine usuários

3. **Antes da Produção**
   - [ ] Monitore 10-20 e-mails de teste
   - [ ] Verifique se vão para Spam
   - [ ] Ajuste filtros se necessário

---

## 📁 Onde Encontrar

### Documentação
```
📦 projeto/
├── 📄 QUICK_START_EMAIL.md          ← Leia primeiro (5 min)
├── 📄 GUIA_EMAIL_LOCAWEB.md         ← Guia completo (20 min)
├── 📄 LOCAWEB_TECNICO.md            ← Referência técnica
├── 📄 README_EMAIL.md               ← Arquitetura
├── 📄 EMAIL_SETUP_SUMMARY.md        ← Resumo executivo
├── 📄 CHECKLIST_IMPLEMENTACAO.md    ← Check cada passo
└── 📄 START_HERE.md                 ← Este arquivo
```

### Código
```
📦 helpers/
├── MailService.php                  ← Novo (SMTP/mail)
├── EmailTemplate.php                ← Novo (Templates HTML)
└── AlertDispatcher.php              ← Modificado
📦 app/controllers/
├── TherapistController.php          ← Modificado
└── PatientPortalController.php      ← Modificado
```

---

## ❓ Dúvidas Rápidas?

**P: Preciso fazer isso agora?**  
R: Para envios automáticos funcionarem, sim. Sem `.env` configurado, Sistema faz fallback para `mail()` do PHP (menos confiável).

**P: Vou quebrar algo?**  
R: Não. Código existente não foi alterado significativamente. Sistema é 100% backwards compatible.

**P: Posso começar em desenvolvimento?**  
R: Sim! Recomendado. Teste tudo antes de produção.

**P: E se eu não conseguir configurar SMTP?**  
R: Sistema ainda funciona via `mail()` do PHP. Mas SMTP Locaweb é **muito mais confiável**.

---

## ⚡ Checklist Rápido

- [ ] `composer install` executado
- [ ] `.env` configurado
- [ ] Teste executado com sucesso
- [ ] Caixa e-mail criada na Locaweb
- [ ] SMTP validado em produção
- [ ] Primeira tarefa enviada e recebida
- [ ] SPF/DKIM configurado (opcional mas recomendado)

---

## 📞 Precisa de Ajuda?

1. **Problema técnico?** → Consulte GUIA_EMAIL_LOCAWEB.md (seção Troubleshooting)
2. **Erro genérico?** → Consulte LOCAWEB_TECNICO.md
3. **How-to?** → Consulte QUICK_START_EMAIL.md
4. **Algo errado no código?** → Verifique README_EMAIL.md

---

## ✨ Sucesso!

Você está a **3 passos** de ter e-mails automáticos profissionais rodando.

### Comande Mágico (faz tudo de uma vez em Dev):
```bash
# 1. Instala
composer install

# 2. Testa
php -r "require 'vendor/autoload.php'; use Config\Config; use Helpers\MailService; Config::loadEnv(); echo (new MailService())->send('seu-email@gmail.com','Teste Sistema','Bem-vindo','<h1>Sistema OK!</h1>') ? '✅ TUDO BEM' : '❌ REVISAR .env';"

# 3. Pronto!
echo "Sistema pronto para uso!"
```

---

**Tempo estimado: 15-20 minutos para estar operacional** ⏱️

🚀 **Vamos começar!**

👉 Próximo passo: Leia **QUICK_START_EMAIL.md**
