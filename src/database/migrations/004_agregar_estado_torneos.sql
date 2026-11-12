-- =========================================================================
-- MIGRACIÓN 004: Estado del Torneo
-- =========================================================================

ALTER TABLE torneos
ADD COLUMN estado ENUM('pendiente', 'en_curso', 'finalizado') NOT NULL DEFAULT 'pendiente' AFTER formato;