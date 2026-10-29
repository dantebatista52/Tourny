-- =========================================================================
-- MIGRACIÓN 006: Cupo Máximo de Equipos por Torneo
-- =========================================================================

ALTER TABLE torneos
ADD COLUMN cupo_maximo INT NOT NULL DEFAULT 16 AFTER formato;