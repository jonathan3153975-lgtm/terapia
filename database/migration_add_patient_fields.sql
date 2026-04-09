-- Migration: Adicionar campos Pai, Mãe e Primeira palavra dos pacientes
-- Data: 2026-04-09
-- Descrição: Adiciona três novos campos à tabela patients para armazenar informações sobre os pais e a primeira palavra que vem à mente

USE terapia;

ALTER TABLE patients ADD COLUMN father VARCHAR(150) NULL COMMENT 'Nome do pai do paciente';
ALTER TABLE patients ADD COLUMN mother VARCHAR(150) NULL COMMENT 'Nome da mãe do paciente';
ALTER TABLE patients ADD COLUMN first_word VARCHAR(150) NULL COMMENT 'A primeira palavra que vem a mente do paciente';

-- Script de rollback (em caso de necessidade):
-- ALTER TABLE patients DROP COLUMN father;
-- ALTER TABLE patients DROP COLUMN mother;
-- ALTER TABLE patients DROP COLUMN first_word;
