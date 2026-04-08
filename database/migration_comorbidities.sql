-- =============================================================
-- Migration: Comorbidades como JSON (substitui colunas TINYINT)
-- Banco: terapia
-- Data: 2026-04-08
--
-- ATENÇÃO: Execute este script ANTES de implantar o novo código
-- da aplicação que utiliza comorbidities_json.
-- Após executado, os campos individuais (alcoholism, drugs,
-- convulsions, smoker, hepatitis, hypertension, diabetes) são
-- removidos da tabela patients.
-- =============================================================

USE terapia;

-- ---------------------------------------------------------------
-- Passo 1: Adicionar a nova coluna comorbidities_json
-- ---------------------------------------------------------------
ALTER TABLE patients
  ADD COLUMN comorbidities_json JSON NULL
  AFTER diabetes;

-- ---------------------------------------------------------------
-- Passo 2: Inicializar com array vazio para todos os registros
-- ---------------------------------------------------------------
UPDATE patients
  SET comorbidities_json = JSON_ARRAY();

-- ---------------------------------------------------------------
-- Passo 3: Migrar dados existentes – adicionar cada comorbidade
--          ao array JSON conforme as colunas TINYINT originais
-- ---------------------------------------------------------------
UPDATE patients
  SET comorbidities_json = JSON_ARRAY_APPEND(comorbidities_json, '$', 'Alcoolismo')
  WHERE alcoholism = 1;

UPDATE patients
  SET comorbidities_json = JSON_ARRAY_APPEND(comorbidities_json, '$', 'Drogas')
  WHERE drugs = 1;

UPDATE patients
  SET comorbidities_json = JSON_ARRAY_APPEND(comorbidities_json, '$', 'Convulsões')
  WHERE convulsions = 1;

UPDATE patients
  SET comorbidities_json = JSON_ARRAY_APPEND(comorbidities_json, '$', 'Fumante')
  WHERE smoker = 1;

UPDATE patients
  SET comorbidities_json = JSON_ARRAY_APPEND(comorbidities_json, '$', 'Hepatite')
  WHERE hepatitis = 1;

UPDATE patients
  SET comorbidities_json = JSON_ARRAY_APPEND(comorbidities_json, '$', 'Hipertensão')
  WHERE hypertension = 1;

UPDATE patients
  SET comorbidities_json = JSON_ARRAY_APPEND(comorbidities_json, '$', 'Diabetes')
  WHERE diabetes = 1;

-- ---------------------------------------------------------------
-- Passo 4: Normalizar registros sem comorbidades para NULL
--          (consistência com addictions_json)
-- ---------------------------------------------------------------
UPDATE patients
  SET comorbidities_json = NULL
  WHERE comorbidities_json = JSON_ARRAY();

-- ---------------------------------------------------------------
-- Passo 5: Remover as colunas TINYINT que foram substituídas
-- ---------------------------------------------------------------
ALTER TABLE patients
  DROP COLUMN alcoholism,
  DROP COLUMN drugs,
  DROP COLUMN convulsions,
  DROP COLUMN smoker,
  DROP COLUMN hepatitis,
  DROP COLUMN hypertension,
  DROP COLUMN diabetes;

-- ---------------------------------------------------------------
-- Verificação pós-migração (opcional – executar para conferir)
-- ---------------------------------------------------------------
-- SELECT id, name, comorbidities_json
-- FROM patients
-- WHERE comorbidities_json IS NOT NULL
-- LIMIT 20;
