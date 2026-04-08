# Orientacao de Alteracoes no Banco de Dados - Escalabilidade

## Objetivo
Este documento orienta a aplicacao das mudancas para suportar os modulos:
- Administrador Geral
- Terapeuta
- Paciente

## Arquivo de migracao
Execute o script:
- `database/migration_scalability_modules.sql`

## Ordem recomendada
1. Fazer backup completo do banco.
2. Executar primeiro `database/migration_patient_fields.sql` (caso ainda nao tenha sido executado).
3. Executar `database/migration_scalability_modules.sql`.
4. Validar integridade dos dados e acessos por perfil.

## Principais alteracoes
- Tabela `users`:
  - Role expandido para `admin`, `super_admin`, `therapist`, `patient`.
  - Novas colunas: `therapist_id`, `patient_id`.
- Tabelas com escopo por terapeuta:
  - `patients`: nova coluna `therapist_id`.
  - `patient_records`: nova coluna `therapist_id` e `description`.
  - `appointments`: nova coluna `therapist_id`.
  - `payments`: nova coluna `therapist_id`.
- Novas tabelas:
  - `patient_tasks` (tarefas atribuidas aos pacientes)
  - `task_attachments` (anexos e links das tarefas)
  - `therapist_files` (controle de arquivos e espaco em disco)
  - `patient_messages` (base para modulo de interacao)

## Mapeamento de dados legados
- Usuarios com role `admin` sao convertidos para `super_admin` no final da migracao.
- Recomendado associar cada paciente existente a um terapeuta (`patients.therapist_id`) antes de liberar acesso em producao.

## Validacoes apos migracao
- Login por perfil:
  - Super admin acessa dashboard geral e cadastro de terapeutas.
  - Terapeuta acessa dashboard do consultorio e apenas seus pacientes.
  - Paciente acessa portal e tarefas recebidas.
- Conferir indicadores:
  - Total de terapeutas
  - Total de pacientes ativos
  - Total de arquivos e uso em disco

## Observacoes importantes
- Alguns comandos `ADD CONSTRAINT` podem falhar se a constraint ja existir em ambiente com ajustes manuais anteriores.
- Em MySQL mais antigo, `IF NOT EXISTS` em `ADD COLUMN`/`ADD INDEX` pode nao ser suportado. Nesse caso, aplique os comandos manualmente verificando existencia antes.
- O modulo de Interacao foi preparado no banco (`patient_messages`), mas a interface foi deixada para etapa futura.
