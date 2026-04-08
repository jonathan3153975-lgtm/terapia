# Sistema SaaS de Gestao para Terapia Sistemica

Implementacao inicial do sistema descrito em sistema.md, com arquitetura MVC em PHP, multi-tenant por terapeuta e tres modulos de acesso.

## Stack
- PHP 8.1+
- MySQL + PDO
- Bootstrap + jQuery + SweetAlert + Quill

## Modulos
- Administrador Geral
- Terapeuta
- Paciente

## Setup rapido
1. Copie .env.example para .env e ajuste credenciais.
2. Instale dependencias:

```bash
composer install
composer dump-autoload
```

3. Execute o schema SQL em database/schema.sql.
4. Aponte o servidor web para a raiz do projeto.

## Entradas
- Login: /index.php?action=login
- Dashboard: /dashboard.php
- Portal do paciente: /patient.php

## Credenciais de teste
- admin@teste.com / 123456
- terapeuta@teste.com / 123456
- paciente@teste.com / 123456

## Escopo implementado agora
- Estrutura MVC base e autenticação multi-perfil.
- Dashboard Admin com indicadores globais.
- Gestao de terapeutas com botao Gerar senha.
- Dashboard Terapeuta com KPIs e cadastro de paciente.
- Busca dinamica na lista de pacientes.
- CEP auto preenchimento no ultimo digito via ViaCEP.
- Area interna com abas de Atendimentos/Tarefas e Quill demonstrado.
- Portal do paciente com dashboard e lista de tarefas.
- Banco completo com tabelas de planos, pagamentos e comissoes.

## Proxima iteracao sugerida
- CRUD completo de terapeutas/pacientes com editar/excluir.
- Fluxo real de atendimentos/tarefas com persistencia de historico.
- Upload real de arquivos com controle de espaco por terapeuta.
- Integracao Mercado Pago (checkout + webhook).
- Logs de acoes e trilha de auditoria.
