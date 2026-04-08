# Sistema SaaS de Gestão para Consultórios de Terapia Sistêmica

## 🎯 Objetivo

Desenvolver um sistema SaaS moderno, escalável e responsivo para gestão
de consultórios de terapia sistêmica, utilizando boas práticas de
desenvolvimento.

------------------------------------------------------------------------

## 🧱 Tecnologias Obrigatórias

-   **Backend:** PHP (POO, MVC, Clean Code)
-   **Banco de Dados:** MySQL (PDO)
-   **Frontend:** HTML5, CSS3, JavaScript
-   **Bibliotecas:**
    -   jQuery
    -   Bootstrap (UI responsiva)
    -   SweetAlert (alertas)
    -   Quill (editor de texto)
-   **Arquitetura:** MVC + SaaS multi-tenant
-   **Pagamentos:** Integração com Mercado Pago

------------------------------------------------------------------------

## 🏗️ Arquitetura do Sistema

-   Sistema SaaS multi-tenant (cada terapeuta possui ambiente isolado)
-   Código limpo e organizado (Clean Code)
-   Separação clara de camadas (Controller, Model, View)
-   Segurança:
    -   Hash de senha (password_hash)
    -   Proteção contra SQL Injection (PDO)
    -   Validação de inputs

------------------------------------------------------------------------

## 👤 Módulos do Sistema

### 1. Administrador Geral

#### Funcionalidades:

-   Cadastro de terapeutas:
    -   Nome
    -   CPF (máscara + validação)
    -   Telefone (máscara BR)
    -   E-mail
-   Gestão de terapeutas:
    -   Visualizar dados
    -   Alterar plano
    -   Ver pagamentos
    -   Redefinir senha
-   Dashboard:
    -   Total de terapeutas
    -   Total de pacientes
    -   Pacientes ativos
    -   Total de arquivos
    -   Espaço em disco utilizado
-   Planos:
    -   Planos para terapeutas (mensal/anual)
    -   Planos para pacientes
-   Comissão:
    -   Sistema de comissão por paciente ativo

------------------------------------------------------------------------

### 2. Terapeuta

#### Dashboard:

-   Total de pacientes
-   Atendimentos registrados
-   Tarefas
-   Mensagens
-   Arquivos armazenados

#### Cadastro de Pacientes:

Campos: - Nome - Data de nascimento (idade automática) - Telefone
(máscara) - E-mail - Estado civil - Filhos (texto)

#### Saúde:

-   Depressão/Ansiedade (checkbox + medicação)
-   Tratamento médico (descrição + medicação)
-   Alcoolismo
-   Drogas
-   Convulsões
-   Fumante
-   Hepatite
-   Hipertensão
-   Diabetes

#### Vícios:

-   Multiselect com checkbox:
    -   Drogas, Jogos, Sexo, Pornografia, Telas, Compras, Comida, Outros

#### Outros:

-   Já fez terapia? (descrição)
-   Data início tratamento
-   Menstruação (texto)
-   Intestino (texto)
-   Queixa principal

------------------------------------------------------------------------

#### Gestão de Pacientes:

-   Listagem com botões:
    -   Editar
    -   Excluir
    -   Visualizar
    -   Acessar
-   Visualizar:
    -   Nova aba
    -   Dados + atendimentos + tarefas

------------------------------------------------------------------------

#### Área do Paciente (Interno):

Abas: - Atendimentos - Tarefas

##### Atendimentos:

-   Listar
-   Criar (data, descrição, histórico com Quill)

##### Tarefas:

-   Listar
-   Criar:
    -   Data
    -   Título
    -   Descrição (Quill avançado)
    -   Anexos (PDF, imagens, links)
    -   Opção de envio ao paciente

------------------------------------------------------------------------

### 3. Paciente

#### Dashboard:

-   Total de sessões
-   Total de tarefas
-   Tarefas pendentes
-   Tarefas concluídas
-   Materiais acessados
-   Mensagem diária (placeholder futuro)

#### Funcionalidades:

-   Listagem de tarefas recebidas

------------------------------------------------------------------------

## 🗄️ Banco de Dados

Criar banco:

    CREATE DATABASE terapia;

### Tabelas principais:

-   usuarios
-   terapeutas
-   pacientes
-   atendimentos
-   tarefas
-   arquivos
-   planos
-   pagamentos
-   comissoes

------------------------------------------------------------------------

## 👥 Usuários de Teste

### Admin:

-   Email: admin@teste.com
-   Senha: 123456

### Terapeuta:

-   Email: terapeuta@teste.com
-   Senha: 123456

### Paciente:

-   Email: paciente@teste.com
-   Senha: 123456

------------------------------------------------------------------------

## 📌 Requisitos Extras

-   Sistema responsivo (mobile-first)
-   Interface limpa e intuitiva
-   Upload de arquivos com controle de espaço
-   Logs de ações
-   Estrutura preparada para expansão

------------------------------------------------------------------------

## 🚀 Próximos Passos

-   Estrutura base do projeto (MVC)
-   Criação do banco completo
-   Autenticação (login multi-perfil)
-   Integração com Mercado Pago
-   Desenvolvimento incremental por módulos
