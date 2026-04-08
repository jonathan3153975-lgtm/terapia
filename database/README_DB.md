# Banco de Dados

## Como criar
1. Crie seu arquivo .env com base em .env.example.
2. Execute o schema:

```sql
source database/schema.sql;
```

## Usuarios de teste
- Admin: admin@teste.com / 123456
- Terapeuta: terapeuta@teste.com / 123456
- Paciente: paciente@teste.com / 123456

## Se o login retornar "Credenciais invalidas"
Em bases que ja existiam, a senha pode nao ter sido atualizada pelo seed antigo.
Execute:

```sql
UPDATE users
SET password = '$2y$12$vM76NPXqrc6Qt9Zg6rGQOeTpDOaYavnj8kRjMAh0FgFGkxNHUgtsq'
WHERE email IN ('admin@teste.com', 'terapeuta@teste.com', 'paciente@teste.com');
```

## Observacao
As tabelas de pagamentos ja estao preparadas para integracao com Mercado Pago (provider e provider_reference).
