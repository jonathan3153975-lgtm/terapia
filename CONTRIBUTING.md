# Diretrizes de desenvolvimento

## Estrutura de Código

### Nomenclatura
- **Classes**: PascalCase (ex: `UserController`, `PatientModel`)
- **Métodos/Funções**: camelCase (ex: `getPatient()`, `createUser()`)
- **Variáveis**: camelCase (ex: `$userData`, `$patientId`)
- **Constantes**: UPPER_SNAKE_CASE (ex: `DATABASE_HOST`, `MAX_UPLOAD_SIZE`)

### Estrutura de Pastas
```
app/
├── controllers/   - Lógica de controle
├── models/       - Acesso a dados
└── views/        - Templates HTML

config/          - Configurações
classes/         - Classes base (Model, Controller)
helpers/         - Funções utilitárias
public/          - Arquivos públicos (CSS, JS, images)
database/        - Scripts SQL
```

## Padrões de Código

### Controllers
```php
<?php
namespace App\Controllers;

use Classes\Controller;
use App\Models\User;
use Helpers\Auth;

class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireAdmin(); // Se necessário
        $this->userModel = new User();
    }

    public function index(): void
    {
        // Lógica aqui
        $this->view('admin/users/index', ['users' => $users]);
    }
}
```

### Models
```php
<?php
namespace App\Models;

use Classes\Model;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        // Implementação
    }
}
```

### Views
```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Head -->
</head>
<body>
    <div class="container">
        <?php foreach($items as $item): ?>
            <div><?php echo htmlspecialchars($item['name']); ?></div>
        <?php endforeach; ?>
    </div>
</body>
</html>
```

## Best Practices

### Segurança
- ✅ Sempre sanitize entrada: `Utils::sanitize()`
- ✅ Use prepared statements: `$this->query($sql, $params)`
- ✅ Valide dados: `Validator::validateCPF()`
- ✅ Hash senhas: `Utils::hashPassword()`

### Performance
- ✅ Use índices no banco de dados
- ✅ Implemente paginação
- ✅ Reutilize queries
- ✅ Cache quando apropriado

### Qualidade
- ✅ Mantenha funções pequenas
- ✅ Use type hints
- ✅ Implemente tratamento de erro
- ✅ Escreva comentários quando necessário

## Git Workflow

```bash
# Criar branch
git checkout -b feature/nome-da-feature

# Commitar
git commit -m "Descrição clara da mudança"

# Push
git push origin feature/nome-da-feature

# Pull Request no GitHub
```

### Mensagens de Commit
- `feat:` Nova funcionalidade
- `fix:` Correção de bug
- `docs:` Documentação
- `style:` Formatação
- `refactor:` Refatoração de código
- `test:` Testes

## Validação de Formulários

### HTML
```html
<input 
    type="email" 
    class="form-control"
    id="email"
    name="email"
    placeholder="seu@email.com"
    required
>
```

### PHP
```php
if (!Utils::isValidEmail($email)) {
    $this->error('Email inválido');
}
```

### JavaScript
```javascript
if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
    Swal.fire({icon: 'error', text: 'Email inválido'});
}
```

## Testing

```bash
phpunit tests/
```

Estruture testes em `tests/` com a mesma estrutura do `app/`.

## Documentação

Mantenha documentação em:
- `README.md` - Visão geral
- `CONTRIBUTING.md` - Como contribuir
- Comentários no código para lógica complexa

## Performance Checklist

- [ ] Imagens otimizadas
- [ ] CSS minificado em produção
- [ ] JavaScript minificado em produção
- [ ] Queries otimizadas
- [ ] Cache implementado onde apropriado
- [ ] Compression ativado no servidor

## Deploy Checklist

- [ ] `.env` configurado corretamente
- [ ] Banco de dados criado
- [ ] Permissões de arquivo corretas
- [ ] HTTPS ativado
- [ ] Error reporting desativado em produção
- [ ] Backups configurados
- [ ] Monitoramento ativado
