# Terapia - Sistema de Administração de Consultório

Um sistema completo e moderno para gerenciar um consultório de terapias, desenvolvido em PHP 8+, JavaScript/jQuery, com Bootstrap e padrão MVC.

## Características

- ✅ **Autenticação Segura** - Login com recuperação de senha
- ✅ **Gerenciamento de Pacientes** - Cadastro com validação de CPF, telefone e CEP
- ✅ **Atendimentos** - Registre notas detalhadas usando editor rich-text (Quill)
- ✅ **Agenda** - Visualize em calendário ou lista, com notificações
- ✅ **Gestão Financeira** - Registre pagamentos e valores
- ✅ **Relatórios Avançados** - Gere relatórios em PDF com mPDF
- ✅ **Responsivo** - Interface totalmente responsiva para desktop, tablet e mobile
- ✅ **Validações** - Máscaras de entrada e validações em tempo real

## Requisitos

- PHP 8.0+
- MySQL 8.0+
- Composer (opcional)
- Navegador moderno (Chrome, Firefox, Safari, Edge)

## Instalação

### 1. Clonar o repositório

```bash
git clone https://github.com/seu-usuario/terapia.git
cd terapia
```

### 2. Configurar banco de dados

#### Option A: Via phpMyAdmin ou MySQL Workbench

1. Acesse sua ferramenta MySQL
2. Crie um novo banco de dados chamado `terapia`
3. Execute o script SQL localizado em `database/schema.sql`

#### Option B: Via linha de comando

```bash
mysql -h terapia.mysql.dbaas.com.br -u terapia -p terapia < database/schema.sql
```

### 3. Configurar credenciais

Edite o arquivo `config/Database.php` com suas credenciais:

```php
private const HOST = 'terapia.mysql.dbaas.com.br';
private const DATABASE = 'terapia';
private const USER = 'terapia';
private const PASSWORD = 'Jonathan315@@';
```

### 4. Configurar URL da aplicação

Edite `config/Config.php`:

```php
public const APP_URL = 'http://seu-dominio.com/terapia';
```

### 5. Acessar a aplicação

```
http://localhost/terapia/index.php
```

## Credenciais Padrão

**Email:** admin@terapia.com
**Senha:** Admin@123

## Estrutura do Projeto

```
terapia/
├── app/
│   ├── controllers/       # Controladores da aplicação
│   ├── models/           # Modelos de dados
│   └── views/            # Templates HTML
├── classes/              # Classes base (Model, Controller)
├── config/               # Configurações
├── database/             # Scripts SQL
├── helpers/              # Funções utilitárias
├── public/
│   ├── css/             # Estilos CSS
│   └── js/              # Scripts JavaScript
├── uploads/             # Arquivos enviados
└── vendor/              # Autoload
```

## Funcionalidades Principais

### 1. Autenticação
- Login com email e senha
- Recuperação de senha por email
- Gerenciamento de sessão

### 2. Pacientes
- Cadastro completo com validação
- Máscara de CPF com cálculo de validação
- Busca automática de endereço via CEP
- Listagem com paginação e busca

### 3. Atendimentos
- Registre notas detalhadas com Quill Editor
- Histórico completo de atendimentos por paciente
- Data e hora do atendimento

### 4. Agenda
- Visualize em calendário (FullCalendar)
- Visualize em lista com filtros
- Agendamentos aprovados e pendentes
- Prevenção de conflitos de horário

### 5. Valores/Pagamentos
- Registre valores com descrição
- Marque como pago ou pendente
- Filtros por status e período
- Relatório de receita

### 6. Relatórios
- Atendimentos por mês/ano
- Atendimentos por paciente
- Valores pagos vs pendentes
- Exportar para PDF (mPDF)

## Tecnologias Utilizadas

### Backend
- PHP 8.0+
- PDO (Database Abstraction)
- mPDF (Geração de PDF)

### Frontend
- HTML5
- Bootstrap 5.3
- jQuery 3.6
- SweetAlert2 (Notificações)
- FullCalendar 6 (Calendário)
- Quill 2 (Rich Text Editor)
- Font Awesome 6 (Ícones)

### Padrões e Práticas
- Arquitetura MVC
- POO (Programação Orientada a Objetos)
- Clean Code
- Validação de dados
- Proteção CSRF

## API Endpoints

### Autenticação
- `POST /index.php?action=process-login` - Fazer login
- `GET /index.php?action=logout` - Fazer logout
- `GET /index.php?action=forgot-password` - Recuperar senha
- `POST /index.php?action=process-forgot-password` - Processar recuperação

### Pacientes
- `GET /dashboard.php?action=patients` - Listar pacientes
- `GET /dashboard.php?action=patients&subaction=create` - Form criação
- `POST /dashboard.php?action=patients&subaction=store` - Criar paciente
- `GET /dashboard.php?action=patients&subaction=show&id=1` - Detalhes
- `GET /dashboard.php?action=patients&subaction=edit&id=1` - Form edição
- `POST /dashboard.php?action=patients&subaction=update` - Atualizar
- `POST /dashboard.php?action=patients&subaction=delete` - Deletar
- `GET /dashboard.php?action=patients&subaction=search-cep` - Buscar CEP

### Atendimentos
- `GET /dashboard.php?action=records&patient_id=1` - Listar atendimentos
- `GET /dashboard.php?action=records&subaction=create&patient_id=1` - Form criação
- `POST /dashboard.php?action=records&subaction=store` - Criar atendimento
- `POST /dashboard.php?action=records&subaction=update` - Atualizar
- `POST /dashboard.php?action=records&subaction=delete` - Deletar

### Agendamentos
- `GET /dashboard.php?action=appointments&subaction=calendar` - Calendário
- `GET /dashboard.php?action=appointments&subaction=list` - Lista agendamentos
- `POST /dashboard.php?action=appointments&subaction=store` - Criar agendamento
- `GET /dashboard.php?action=appointments&subaction=get-by-date` - Agendamentos de um dia (JSON)

### Pagamentos
- `GET /dashboard.php?action=payments` - Listar pagamentos
- `POST /dashboard.php?action=payments&subaction=store` - Criar pagamento
- `POST /dashboard.php?action=payments&subaction=update` - Atualizar pagamento

### Relatórios
- `GET /dashboard.php?action=reports` - Dashboard de relatórios
- `GET /dashboard.php?action=reports&subaction=records` - Atendimentos
- `GET /dashboard.php?action=reports&subaction=patient-records` - Por paciente
- `GET /dashboard.php?action=reports&subaction=payments` - Valores
- `GET /dashboard.php?action=reports&subaction=annual` - Anual

## Validações

### CPF
- 11 dígitos
- Cálculo de validação automático
- Máscara: 000.000.000-00

### Telefone
- 10 ou 11 dígitos
- Máscara: (00) 0000-0000 ou (00) 90000-0000

### CEP
- 8 dígitos
- Busca automática de dados (Via API)
- Máscara: 00000-000

### Senha
- Mínimo 8 caracteres
- Deve conter letra maiúscula
- Deve conter letra minúscula
- Deve conter número
- Deve conter caractere especial

## Segurança

- ✅ Senhas com hash bcrypt
- ✅ Sessions com timeout
- ✅ Proteção CSRF
- ✅ Sanitização de entrada
- ✅ SQL Injection prevention (Prepared Statements)
- ✅ XSS protection (htmlspecialchars)

## Configuração de Email

Para ativar recuperação de senha por email, edite `helpers/Auth.php` e configure:

```php
// Adicione a função sendResetEmail()
private function sendResetEmail($email, $token) {
    $resetLink = \Config\Config::APP_URL . "/index.php?action=reset-password&token=" . $token;
    $subject = "Recuperação de Senha - Terapia";
    $message = "Clique no link para redefinir sua senha: " . $resetLink;
    
    mail($email, $subject, $message);
}
```

## Troubleshooting

### Erro de conexão com banco de dados
- Verifique as credenciais em `config/Database.php`
- Certifique-se de que o banco `terapia` foi criado
- Verifique a permissão do usuário MySQL

### Erro 404 nas rotas
- Verifique se o arquivo `dashboard.php` existe na raiz
- Confirme o valor de `APP_URL` em `config/Config.php`
- Verifique as permissões de arquivo

### Erro na busca de CEP
- A API de CEP (ViaCEP) deve estar acessível
- Verifique sua conexão de internet
- Certifique-se de que o CEP é válido

## Roadmap

- [ ] Autenticação com dois fatores
- [ ] Integração com WhatsApp para lembretes
- [ ] App mobile (React Native)
- [ ] Backup automático do banco
- [ ] Sistema de permissões por função
- [ ] Agendamento automático de lembretes
- [ ] Chat entre paciente e terapeuta
- [ ] Prontuário eletrônico integrado

## Contribuindo

1. Faça um Fork do projeto
2. Crie um branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para o branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## Licença

Este projeto está sob a licença MIT. Veja [LICENSE](LICENSE) para mais detalhes.

## Suporte

Para suporte, entre em contato através de:
- Email: support@terapia.com
- Issues do GitHub: [GitHub Issues](https://github.com/seu-usuario/terapia/issues)

## Autores

- **Desenvolvedor** - [GitHub](https://github.com/seu-usuario)

---

Desenvolvido com ❤️ para consultórios de terapias
