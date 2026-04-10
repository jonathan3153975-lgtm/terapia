# ✅ CHECKLIST - Implementação de E-mail Automático

## 📦 Instalação & Configuração

- [ ] Execute `composer install` para instalar PHPMailer
  ```bash
  composer install
  ```

- [ ] Crie/Configure arquivo `.env` na raiz do projeto
  - [ ] Adicione `MAIL_DRIVER=smtp`
  - [ ] Adicione `MAIL_HOST=smtplw.com.br`
  - [ ] Adicione `MAIL_PORT=587`
  - [ ] Adicione `MAIL_ENCRYPTION=tls`
  - [ ] Adicione `MAIL_USERNAME=seu-email@seu-dominio.com.br`
  - [ ] Adicione `MAIL_PASSWORD=sua-senha`
  - [ ] Adicione `MAIL_FROM_ADDRESS=seu-email@seu-dominio.com.br`
  - [ ] Adicione `MAIL_FROM_NAME=Seu Nome/Clínica`

## 🔐 Configuração Locaweb

- [ ] Acesse https://centraldocliente.locaweb.com.br/
- [ ] Crie nova caixa postal
  - [ ] Email: `seu-email@seu-dominio.com.br`
  - [ ] Defina senha forte
  - [ ] Confirme criação
- [ ] Anote as credenciais

## 🌐 DNS (Opcional mas Recomendado)

- [ ] Configure SPF record
  - [ ] Vá a: Domínios → Zona DNS
  - [ ] Novo TXT record: `v=spf1 include:locaweb.com.br ~all`
  - [ ] Salve

- [ ] Configure DKIM (se disponível)
  - [ ] Solicite chave na Locaweb
  - [ ] Adicione TXT record com chave pública
  - [ ] Salve

- [ ] Configure DMARC (opcional)
  - [ ] Novo TXT record: `v=DMARC1; p=quarantine; rua=mailto:seu-email@seu-dominio.com.br`
  - [ ] Salve

- [ ] Aguarde propagação de DNS (até 48h)

## 🧪 Testes

- [ ] Crie arquivo `test-email.php` na raiz
  ```php
  <?php
  require __DIR__ . '/vendor/autoload.php';
  use Config\Config;
  use Helpers\MailService;
  
  Config::loadEnv();
  $mail = new MailService();
  $ok = $mail->send('seu-email@gmail.com', 'Teste', 'Teste', '<h1>OK!</h1>');
  echo $ok ? '✅' : '❌';
  ?>
  ```

- [ ] Execute teste: `php test-email.php`
- [ ] Verifique se recebeu email
- [ ] Verifique se não foi para SPAM (configure filtros se necessário)

- [ ] Teste completo no sistema:
  - [ ] Crie tarefa de teste
  - [ ] Marque "Enviar ao Paciente"
  - [ ] Verifique se paciente recebeu email
  - [ ] Verifique layout HTML

- [ ] Teste devolutiva:
  - [ ] Faça login como paciente
  - [ ] Responda tarefa
  - [ ] Verifique se terapeuta recebeu notificação

## 📝 Validação de Código

- [ ] Todos arquivos PHP sem erros:
  ```bash
  php -l helpers/MailService.php
  php -l helpers/EmailTemplate.php
  php -l app/controllers/TherapistController.php
  php -l app/controllers/PatientPortalController.php
  ```

- [ ] Verifique se todos os testes passaram

## 📊 Monitoramento

- [ ] Monitore arquivo de log de erros:
  ```bash
  tail -f /var/log/php-errors.log
  ```

- [ ] Configure alertas para falhas de envio
- [ ] Mantenha registro de emails enviados

## 👥 Treinamento de Usuários

- [ ] Mostre ao terapeuta como enviar tarefas com email
- [ ] Mostre ao paciente como receber notificações
- [ ] Explique que emails podem ir para spam (adicionar aos contatos)

## 🚀 Deploy em Produção

- [ ] Backup do banco de dados
- [ ] Upload dos arquivos modificados:
  - [ ] `helpers/MailService.php`
  - [ ] `helpers/EmailTemplate.php`
  - [ ] `helpers/AlertDispatcher.php` (modificado)
  - [ ] `app/controllers/TherapistController.php` (modificado)
  - [ ] `app/controllers/PatientPortalController.php` (modificado)
  - [ ] `composer.json` (modificado)

- [ ] Execute `composer install` no servidor de produção

- [ ] Configure `.env` em produção com credenciais Locaweb

- [ ] Teste novamente em produção:
  - [ ] Crie tarefa teste
  - [ ] Verifique email
  - [ ] Verifique logs

- [ ] Monitore os primeiros emails enviados

## 🎓 Documentação

- [ ] Leia `EMAIL_SETUP_SUMMARY.md` - Resumo geral
- [ ] Leia `GUIA_EMAIL_LOCAWEB.md` - Guia completo
- [ ] Leia `QUICK_START_EMAIL.md` - Para colaboradores
- [ ] Leia `LOCAWEB_TECNICO.md` - Referência técnica

## 🔧 Troubleshooting

Se tiver problemas:

- [ ] Verifique `.env` - credenciais corretas?
- [ ] Verifique SMTP - porta bloqueada? Tente porta 465
- [ ] Verifique logs - há mensagens de erro?
- [ ] Teste conexão: `telnet smtplw.com.br 587`
- [ ] Verifique SPF/DKIM - email indo para spam?
- [ ] Contacte Locaweb se ainda tiver problemas

## 📞 Referências Rápidas

- **Central Locaweb**: https://centraldocliente.locaweb.com.br/
- **Ajuda SMTP**: https://www.locaweb.com.br/ajuda/categorias/smtp-locaweb/
- **Telefone**: 0800 770 2245 ou (11) 3544-0500

## ✨ Sucesso!

- [ ] Sistema funcionando em desenvolvimento
- [ ] Sistema funcionando em produção
- [ ] Usuários treinados
- [ ] Documentação acessível
- [ ] Monitoramento ativo

---

**Status**: ⏳ Em Construção  
**Última Atualização**: 10 de Abril de 2026

Marque cada item conforme avança! 🎯
