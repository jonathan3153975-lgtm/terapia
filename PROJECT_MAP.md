# 📁 Estrutura do Projeto - Mapa de Arquivos

## Hierarquia Completa

```
terapia/
├── 📄 index.php                          # Ponto de entrada (autenticação)
├── 📄 dashboard.php                      # Painel administrativo (roteamento)
│
├── 📁 config/
│   ├── 📄 Database.php                   # Conexão PDO (Singleton)
│   ├── 📄 Config.php                     # Configurações globais
│   └── 📄 .env.example                   # Template de variáveis de ambiente
│
├── 📁 classes/
│   ├── 📄 Model.php                      # Classe abstrata base para modelos
│   └── 📄 Controller.php                 # Classe abstrata base para controllers
│
├── 📁 app/
│   ├── 📁 models/
│   │   ├── 📄 User.php                   # Modelo: Usuários/Administradores
│   │   ├── 📄 Patient.php                # Modelo: Pacientes
│   │   ├── 📄 PatientRecord.php          # Modelo: Registros de atendimento
│   │   ├── 📄 Appointment.php            # Modelo: Agendamentos
│   │   └── 📄 Payment.php                # Modelo: Pagamentos/Valores
│   │
│   ├── 📁 controllers/
│   │   ├── 📄 AuthController.php         # Controle: Autenticação
│   │   ├── 📄 PatientController.php      # Controle: Pacientes
│   │   ├── 📄 PatientRecordController.php # Controle: Registros de atendimento
│   │   ├── 📄 AppointmentController.php  # Controle: Agendamentos
│   │   ├── 📄 PaymentController.php      # Controle: Pagamentos
│   │   ├── 📄 ReportController.php       # Controle: Relatórios
│   │   └── 📄 DashboardController.php    # Controle: Dashboard
│   │
│   └── 📁 views/
│       ├── 📁 auth/
│       │   ├── 📄 login.php              # Tela de login
│       │   ├── 📄 forgot-password.php    # Recuperação de senha
│       │   └── 📄 reset-password.php     # Resetar senha
│       │
│       └── 📁 admin/
│           ├── 📁 patients/
│           │   ├── 📄 index.php          # Listar pacientes
│           │   ├── 📄 create.php         # Criar paciente
│           │   ├── 📄 show.php           # Detalhari paciente
│           │   └── 📄 edit.php           # Editar paciente
│           │
│           ├── 📁 records/
│           │   ├── 📄 index.php          # Listar atendimentos
│           │   ├── 📄 create.php         # Novo atendimento
│           │   └── 📄 show.php           # Detalhari atendimento
│           │
│           ├── 📁 appointments/
│           │   ├── 📄 calendar.php       # Calendário de agendamentos
│           │   ├── 📄 list.php           # Lista de agendamentos
│           │   └── 📄 create.php         # Novo agendamento
│           │
│           ├── 📁 payments/
│           │   ├── 📄 index.php          # Listar pagamentos
│           │   ├── 📄 create.php         # Novo pagamento
│           │   └── 📄 edit.php           # Editar pagamento
│           │
│           ├── 📁 reports/
│           │   ├── 📄 index.php          # Dashboard de relatórios
│           │   ├── 📄 records.php        # Relatório de atendimentos
│           │   ├── 📄 appointments.php   # Relatório de agendamentos
│           │   ├── 📄 payments.php       # Relatório de pagamentos
│           │   └── 📄 annual.php         # Relatório anual
│           │
│           └── 📄 dashboard.php          # Dashboard principal
│
├── 📁 helpers/
│   ├── 📄 Utils.php                      # Utilitários gerais
│   ├── 📄 Validator.php                  # Validação e formatação
│   ├── 📄 Session.php                    # Gerenciamento de sessão
│   └── 📄 Auth.php                       # Autenticação e autorização
│
├── 📁 public/
│   ├── 📁 css/
│   │   ├── 📄 dashboard.css              # Estilos principais (600+ linhas)
│   │   └── 📄 additional.css             # Estilos complementares
│   │
│   └── 📁 js/
│       └── 📄 main.js                    # JavaScript utilitário (400+ linhas)
│
├── 📁 database/
│   └── 📄 schema.sql                     # Script de criação do banco
│
├── 📁 uploads/
│   └── 📄 index.html                     # Documentação de uploads
│
├── 📁 vendor/
│   └── 📄 autoload.php                   # Autoloader PSR-4
│
├── 📁 docs/
│   └── 📄 API.md                         # Documentação de endpoints
│
├── 🔧 .htaccess                          # Configuração Apache
├── 📝 .gitignore                         # Ignorar arquivos do Git
├── 📝 composer.json                      # Dependências do projeto
│
└── 📋 Documentação/
    ├── 📄 README.md                      # Visão geral do projeto
    ├── 📄 INSTALL.md                     # Guia de instalação
    ├── 📄 CONTRIBUTING.md                # Diretrizes de desenvolvimento
    ├── 📄 FEATURES.md                    # Checklist de features
    └── 📄 DEVELOPMENT.md                 # Notas de desenvolvimento
```

## 📊 Estatísticas do Projeto

| Aspecto | Quantidade |
|---------|-----------|
| **Arquivos PHP** | 27 |
| **Views (HTML)** | 12+ |
| **Modelos** | 5 |
| **Controladores** | 7 |
| **Helpers/Utilitários** | 4 |
| **Linhas de Código PHP** | 2.500+ |
| **Linhas de CSS** | 1.000+ |
| **Linhas de JavaScript** | 400+ |
| **Linhas de Documentação** | 1.500+ |
| **Arquivos de Configuração** | 8 |
| **Total de Arquivos** | 60+ |

## 🗂️ Diretórios por Responsabilidade

### 📌 Camada de Apresentação
- `app/views/` - Templates HTML
- `public/css/` - Estilos CSS
- `public/js/` - JavaScript frontend

### 📌 Camada de Lógica de Negócios
- `app/controllers/` - Controladores (intermediários entre views e models)
- `app/models/` - Modelos (acesso aos dados)
- `helpers/` - Funções auxiliares

### 📌 Camada de Dados
- `database/` - Schema e scripts SQL
- `config/Database.php` - Conexão com banco

### 📌 Configuração e Entrada
- `config/` - Arquivos de configuração
- `index.php` - Ponto de entrada público
- `dashboard.php` - Ponto de entrada administrativo

### 📌 Dependências e Autoload
- `vendor/` - Dependências do Composer
- `composer.json` - Definição de dependências

### 📌 Documentação
- Raiz do projeto - Guias e documentação
- `docs/` - Documentação técnica adicional

## 📝 Fluxo de Requisições

```
┌─────────────────────┐
│  Cliente (Browser)  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  index.php ou       │
│  dashboard.php      │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  $_GET['action']?   │
│  Roteamento         │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Controller         │
│  (Business Logic)   │
└──────────┬──────────┘
           │
           ▼
┌──────────┴──────────┐
│                     │
▼                     ▼
Model          Helper/Utils
│                     │
▼                     │
┌──────────┬──────────┘
│          │
│      Database
│          │
└──────────┴──────────┘
           │
           ▼
┌─────────────────────┐
│  Response JSON ou   │
│  View HTML          │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Cliente (Browser)  │
└─────────────────────┘
```

## 🔑 Conceitos Arquiteturais

### Padrão MVC Implementado
- **Model** (`app/models/`) - Representa dados e lógica da aplicação
- **View** (`app/views/`) - Apresentação para o usuário
- **Controller** (`app/controllers/`) - Intermediário entre View e Model

### Padrões de Design Utilizados

1. **Singleton** - Database (uma única instância de conexão)
2. **Abstract Factory** - Model e Controller bases
3. **Repository** - Models fornecem interface para dados
4. **Service** - Controllers fornecem lógica de negócio
5. **Builder** - Helpers constroem valores formatados
6. **Observer** - Sessão gerencia estado global

### Princípios Aplicados

- **DRY** (Don't Repeat Yourself) - Código reutilizável
- **SOLID**
  - Single Responsibility - Controllers, Models, Helpers com uma responsabilidade
  - Open/Closed - Classes abertas à extensão, fechadas à modificação
  - Dependency Injection - Banco de dados injetado onde necessário
  - Interface Segregation - Métodos pequenos e focados
  - Dependency Inversion - Depender de abstrações (bases)

## 🔄 Fluxos Principais

### Login
```
login.php (View)
    ↓
AuthController::processLogin()
    ↓
User::findByEmail()
    ↓
password_verify()
    ↓
Auth::login() → Session
    ↓
Redireciona para dashboard.php
```

### Criar Paciente
```
patients/create.php (View)
    ↓
PatientController::store()
    ↓
Validator::validateCPF() e Validator::validatePhone()
    ↓
Patient::createPatient()
    ↓
INSERT INTO patients
    ↓
JSON response ou redireciona
```

### Relatório Mensal
```
ReportController::index()
    ↓
Payment::getByMonth()
Appointment::findBetweenDates()
Patient::findAll()
    ↓
reports/index.php (View)
    ↓
Renderiza gráficos e tabelas
```

## 🎯 Próximos Passos de Desenvolvimento

1. **Completar Views** - Templates .php dos módulos
2. **Integração FullCalendar** - Eventos no calendário
3. **Integração Quill** - Editor Rich Text
4. **mPDF** - Exportação de PDF
5. **Email** - Recuperação de senha
6. **Dashboard Paciente** - Portal do paciente
7. **Testes** - PHPUnit tests
8. **Deployment** - Hosting e CI/CD

---

**Navegando no Projeto:**
- Modelos em `app/models/` para lógica de dados
- Controladores em `app/controllers/` para lógica de negócio
- Views em `app/views/` para templates
- Helpers em `helpers/` para funções utilitárias
- Estilos em `public/css/`
- Scripts em `public/js/`
