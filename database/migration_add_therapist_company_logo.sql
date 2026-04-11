-- Migration: campo de logo da empresa para terapeuta
-- Data: 2026-04-11

USE terapia;

ALTER TABLE users
  ADD COLUMN company_logo_name VARCHAR(255) NULL AFTER plan_type,
  ADD COLUMN company_logo_path VARCHAR(500) NULL AFTER company_logo_name;
