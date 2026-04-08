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

## Observacao
As tabelas de pagamentos ja estao preparadas para integracao com Mercado Pago (provider e provider_reference).
