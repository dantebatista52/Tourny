-- =========================================================================
-- MIGRACIÓN 007: Estadísticas de Jugadores por Partido (Planilla)
-- Proyecto: Tourny
-- =========================================================================

CREATE TABLE IF NOT EXISTS partido_jugador_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_partido INT NOT NULL,
    id_usuario INT NOT NULL,
    id_equipo INT NOT NULL,
    goles INT NOT NULL DEFAULT 0,
    asistencias INT NOT NULL DEFAULT 0,
    amarillas INT NOT NULL DEFAULT 0,
    rojas INT NOT NULL DEFAULT 0,
    
    -- Restricciones de Claves Foráneas (FK)
    CONSTRAINT fk_stats_partido 
        FOREIGN KEY (id_partido) REFERENCES partidos(id) 
        ON DELETE CASCADE,
        
    CONSTRAINT fk_stats_usuario 
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id) 
        ON DELETE CASCADE,

    CONSTRAINT fk_stats_equipo 
        FOREIGN KEY (id_equipo) REFERENCES equipos(id) 
        ON DELETE CASCADE,

    -- Un jugador solo puede tener un registro de estadísticas por partido
    CONSTRAINT uk_partido_usuario UNIQUE (id_partido, id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;