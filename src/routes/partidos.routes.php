<?php

use Slim\App;
use Slim\Views\PhpRenderer;
use App\Middlewares\AuthMiddleware;

/** @var App $app */
/** @var PhpRenderer $renderer */
/** @var Database $database */

// ==========================================
// 5. PARTIDOS, FIXTURE Y ESTADÍSTICAS
// ==========================================

// Generar Fixture
$app->post("/torneos/{id}/fixture/generar", function ($request, $response, $args) {
    $idTorneo = $args['id'];
    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $stmtTorneo = $db->prepare("SELECT * FROM torneos WHERE id = ?");
    $stmtTorneo->execute([$idTorneo]);
    $torneo = $stmtTorneo->fetch();

    if (!$torneo) {
        $response->getBody()->write("Torneo no encontrado.");
        return $response->withStatus(404);
    }

    $stmt = $db->prepare("SELECT id FROM equipos WHERE id_torneo = ?");
    $stmt->execute([$idTorneo]);
    $equipos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $cantEquipos = count($equipos);

    if ($torneo['formato'] === 'eliminatoria') {
        $potenciasValidas = [2, 4, 8, 16, 32];
        if (!in_array($cantEquipos, $potenciasValidas, true)) {
            $response->getBody()->write("Para el formato de Eliminación Directa debes registrar exactamente 2, 4, 8, 16 o 32 equipos. Actualmente tienes {$cantEquipos}.");
            return $response->withStatus(400);
        }
    } else {
        if ($cantEquipos < 2) {
            $response->getBody()->write("Necesitas al menos 2 equipos para generar el fixture de una liga.");
            return $response->withStatus(400);
        }
    }

    $stmtDelete = $db->prepare("DELETE FROM partidos WHERE id_torneo = ?");
    $stmtDelete->execute([$idTorneo]);

    if ($torneo['formato'] === 'eliminatoria') {
        shuffle($equipos);
        for ($i = 0; $i < $cantEquipos; $i += 2) {
            $local = $equipos[$i];
            $visitante = $equipos[$i + 1];

            $stmtInsert = $db->prepare("INSERT INTO partidos (id_torneo, id_equipo_local, id_equipo_visitante, fecha_numero) VALUES (?, ?, ?, 1)");
            $stmtInsert->execute([$idTorneo, $local, $visitante]);
        }
    } else {
        if ($cantEquipos % 2 !== 0) {
            $equipos[] = null;
            $cantEquipos++;
        }

        $jornadas = $cantEquipos - 1;
        $partidosPorJornada = $cantEquipos / 2;

        for ($jornada = 1; $jornada <= $jornadas; $jornada++) {
            for ($i = 0; $i < $partidosPorJornada; $i++) {
                $local = $equipos[$i];
                $visitante = $equipos[$cantEquipos - 1 - $i];

                if ($local === null && $visitante === null) {
                    continue;
                }

                $stmtInsert = $db->prepare("INSERT INTO partidos (id_torneo, id_equipo_local, id_equipo_visitante, fecha_numero) VALUES (?, ?, ?, ?)");
                $stmtInsert->execute([$idTorneo, $local, $visitante, $jornada]);
            }

            $ultimoEquipo = array_pop($equipos);
            array_splice($equipos, 1, 0, [$ultimoEquipo]);
        }
    }

    return $response->withHeader('Location', "/torneos/{$idTorneo}/fixture")->withStatus(302);
})->add(AuthMiddleware::class);

// Ver Fixture
$app->get("/torneos/{id}/fixture", function ($request, $response, $args) use ($renderer) {
    $idTorneo = $args['id'];
    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $stmtTorneo = $db->prepare("SELECT * FROM torneos WHERE id = ?");
    $stmtTorneo->execute([$idTorneo]);
    $torneo = $stmtTorneo->fetch();

    $sql = "SELECT p.*, 
                   el.nombre AS local_nombre, 
                   ev.nombre AS visitante_nombre 
            FROM partidos p
            LEFT JOIN equipos el ON p.id_equipo_local = el.id
            LEFT JOIN equipos ev ON p.id_equipo_visitante = ev.id
            WHERE p.id_torneo = ?
            ORDER BY p.fecha_numero ASC, p.id ASC";

    $stmtPartidos = $db->prepare($sql);
    $stmtPartidos->execute([$idTorneo]);
    $partidos = $stmtPartidos->fetchAll();

    return view($renderer, $response, "partidos/fixture.php", [
        "torneo" => $torneo,
        "partidos" => $partidos
    ]);
})->add(AuthMiddleware::class);

// Guardar Resultado de Partido
$app->post("/partidos/{id}/resultado", function ($request, $response, $args) {
    $idPartido = $args['id'];
    $parsedBody = $request->getParsedBody();
    
    $golesLocal = $parsedBody['goles_local'] ?? null;
    $golesVisitante = $parsedBody['goles_visitante'] ?? null;

    if ($golesLocal === null || $golesVisitante === null) {
        $response->getBody()->write("Debes ingresar los goles de ambos equipos.");
        return $response->withStatus(400);
    }

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $stmtPartido = $db->prepare("SELECT id_torneo FROM partidos WHERE id = ?");
    $stmtPartido->execute([$idPartido]);
    $partido = $stmtPartido->fetch();

    if (!$partido) {
        $response->getBody()->write("Partido no encontrado.");
        return $response->withStatus(404);
    }

    $stmt = $db->prepare("UPDATE partidos SET goles_local = ?, goles_visitante = ?, estado = 'finalizado' WHERE id = ?");
    $stmt->execute([$golesLocal, $golesVisitante, $idPartido]);

    return $response->withHeader('Location', "/torneos/{$partido['id_torneo']}/fixture")->withStatus(302);
})->add(AuthMiddleware::class);

// Ver Tabla de Posiciones
$app->get("/torneos/{id}/tabla", function ($request, $response, $args) use ($renderer) {
    $idTorneo = $args['id'];
    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $stmtTorneo = $db->prepare("SELECT * FROM torneos WHERE id = ?");
    $stmtTorneo->execute([$idTorneo]);
    $torneo = $stmtTorneo->fetch();

    if (!$torneo) {
        $response->getBody()->write("Torneo no encontrado.");
        return $response->withStatus(404);
    }

    $stmtEquipos = $db->prepare("SELECT id, nombre FROM equipos WHERE id_torneo = ?");
    $stmtEquipos->execute([$idTorneo]);
    $equipos = $stmtEquipos->fetchAll();

    $tabla = [];
    foreach ($equipos as $eq) {
        $tabla[$eq['id']] = [
            'nombre' => $eq['nombre'],
            'pj' => 0, 'pg' => 0, 'pe' => 0, 'pp' => 0,
            'gf' => 0, 'gc' => 0, 'dg' => 0, 'pts' => 0
        ];
    }

    $stmtPartidos = $db->prepare("
        SELECT * FROM partidos 
        WHERE id_torneo = ? 
          AND goles_local IS NOT NULL 
          AND goles_visitante IS NOT NULL 
          AND id_equipo_local IS NOT NULL 
          AND id_equipo_visitante IS NOT NULL
    ");
    $stmtPartidos->execute([$idTorneo]);
    $partidos = $stmtPartidos->fetchAll();

    foreach ($partidos as $p) {
        $idLocal = $p['id_equipo_local'];
        $idVisitante = $p['id_equipo_visitante'];
        $golesL = (int)$p['goles_local'];
        $golesV = (int)$p['goles_visitante'];

        if (!isset($tabla[$idLocal]) || !isset($tabla[$idVisitante])) {
            continue;
        }

        $tabla[$idLocal]['pj']++;
        $tabla[$idVisitante]['pj']++;

        $tabla[$idLocal]['gf'] += $golesL;
        $tabla[$idLocal]['gc'] += $golesV;
        $tabla[$idVisitante]['gf'] += $golesV;
        $tabla[$idVisitante]['gc'] += $golesL;

        if ($golesL > $golesV) {
            $tabla[$idLocal]['pg']++;
            $tabla[$idLocal]['pts'] += 3;
            $tabla[$idVisitante]['pp']++;
        } elseif ($golesV > $golesL) {
            $tabla[$idVisitante]['pg']++;
            $tabla[$idVisitante]['pts'] += 3;
            $tabla[$idLocal]['pp']++;
        } else {
            $tabla[$idLocal]['pe']++;
            $tabla[$idLocal]['pts'] += 1;
            $tabla[$idVisitante]['pe']++;
            $tabla[$idVisitante]['pts'] += 1;
        }
    }

    foreach ($tabla as &$e) {
        $e['dg'] = $e['gf'] - $e['gc'];
    }
    unset($e);

    usort($tabla, function ($a, $b) {
        if ($b['pts'] !== $a['pts']) {
            return $b['pts'] <=> $a['pts'];
        }
        if ($b['dg'] !== $a['dg']) {
            return $b['dg'] <=> $a['dg'];
        }
        return $b['gf'] <=> $a['gf'];
    });

    return view($renderer, $response, "torneos/tabla.php", [
        "torneo" => $torneo,
        "tabla" => $tabla
    ]);
})->add(AuthMiddleware::class);

// Ver Planilla de Estadísticas (GET - Organizador)
$app->get("/partidos/{id}/planilla", function ($request, $response, $args) use ($renderer) {
    $idPartido = $args['id'];
    $idUsuarioActual = $_SESSION['usuario_id'];

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $sqlPartido = "SELECT p.*, 
                          t.id_organizador, 
                          t.nombre AS torneo_nombre,
                          el.nombre AS local_nombre, 
                          ev.nombre AS visitante_nombre
                   FROM partidos p
                   JOIN torneos t ON p.id_torneo = t.id
                   LEFT JOIN equipos el ON p.id_equipo_local = el.id
                   LEFT JOIN equipos ev ON p.id_equipo_visitante = ev.id
                   WHERE p.id = ?";
    $stmtPartido = $db->prepare($sqlPartido);
    $stmtPartido->execute([$idPartido]);
    $partido = $stmtPartido->fetch(PDO::FETCH_ASSOC);

    if (!$partido) {
        $response->getBody()->write("Partido no encontrado.");
        return $response->withStatus(404);
    }

    if ($partido['id_organizador'] != $idUsuarioActual) {
        $_SESSION['flash_error'] = "No tienes permisos para administrar la planilla de este partido.";
        return $response->withHeader('Location', "/torneos/{$partido['id_torneo']}/fixture")->withStatus(302);
    }

    $jugadoresLocal = [];
    if (!empty($partido['id_equipo_local'])) {
        $sqlLocal = "SELECT u.id, u.nombre, ej.dorsal, ej.posicion 
                     FROM equipo_jugadores ej
                     JOIN usuarios u ON ej.id_usuario = u.id
                     WHERE ej.id_equipo = ?
                     ORDER BY ej.dorsal ASC, u.nombre ASC";
        $stmtLocal = $db->prepare($sqlLocal);
        $stmtLocal->execute([$partido['id_equipo_local']]);
        $jugadoresLocal = $stmtLocal->fetchAll(PDO::FETCH_ASSOC);
    }

    $jugadoresVisitante = [];
    if (!empty($partido['id_equipo_visitante'])) {
        $sqlVisitante = "SELECT u.id, u.nombre, ej.dorsal, ej.posicion 
                         FROM equipo_jugadores ej
                         JOIN usuarios u ON ej.id_usuario = u.id
                         WHERE ej.id_equipo = ?
                         ORDER BY ej.dorsal ASC, u.nombre ASC";
        $stmtVisitante = $db->prepare($sqlVisitante);
        $stmtVisitante->execute([$partido['id_equipo_visitante']]);
        $jugadoresVisitante = $stmtVisitante->fetchAll(PDO::FETCH_ASSOC);
    }

    if (empty($jugadoresLocal) && empty($jugadoresVisitante)) {
        $_SESSION['flash_error'] = "Aviso: Ninguno de los equipos tiene jugadores fichados todavía. Comparte el código de invitación del equipo para que puedan unirse antes de cargar estadísticas.";
    }

    $stmtStats = $db->prepare("SELECT * FROM partido_jugador_stats WHERE id_partido = ?");
    $stmtStats->execute([$idPartido]);
    $statsGuardadas = [];
    foreach ($stmtStats->fetchAll(PDO::FETCH_ASSOC) as $st) {
        $statsGuardadas[$st['id_usuario']] = $st;
    }

    return view($renderer, $response, "partidos/planilla.php", [
        "partido" => $partido,
        "jugadoresLocal" => $jugadoresLocal,
        "jugadoresVisitante" => $jugadoresVisitante,
        "statsGuardadas" => $statsGuardadas
    ]);
})->add(AuthMiddleware::class);

// Guardar o Actualizar Planilla (POST - Organizador)
$app->post("/partidos/{id}/planilla", function ($request, $response, $args) {
    $idPartido = $args['id'];
    $idUsuarioActual = $_SESSION['usuario_id'];
    $parsedBody = $request->getParsedBody();

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $sqlCheck = "SELECT p.id_torneo, t.id_organizador 
                 FROM partidos p 
                 JOIN torneos t ON p.id_torneo = t.id 
                 WHERE p.id = ?";
    $stmtCheck = $db->prepare($sqlCheck);
    $stmtCheck->execute([$idPartido]);
    $partidoInfo = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$partidoInfo) {
        $response->getBody()->write("Partido no encontrado.");
        return $response->withStatus(404);
    }

    if ($partidoInfo['id_organizador'] != $idUsuarioActual) {
        $_SESSION['flash_error'] = "No tienes permisos para modificar estas estadísticas.";
        return $response->withHeader('Location', "/torneos/{$partidoInfo['id_torneo']}/fixture")->withStatus(302);
    }

    $estadisticas = $parsedBody['stats'] ?? [];

    $db->beginTransaction();
    try {
        $sqlUpsert = "INSERT INTO partido_jugador_stats 
                        (id_partido, id_usuario, id_equipo, goles, asistencias, amarillas, rojas)
                      VALUES (?, ?, ?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE 
                        goles = VALUES(goles),
                        asistencias = VALUES(asistencias),
                        amarillas = VALUES(amarillas),
                        rojas = VALUES(rojas)";
        $stmtUpsert = $db->prepare($sqlUpsert);

        foreach ($estadisticas as $idUsuario => $data) {
            $idEquipo = !empty($data['id_equipo']) ? (int)$data['id_equipo'] : null;
            $goles = max(0, (int)($data['goles'] ?? 0));
            $asistencias = max(0, (int)($data['asistencias'] ?? 0));
            $amarillas = max(0, (int)($data['amarillas'] ?? 0));
            $rojas = max(0, (int)($data['rojas'] ?? 0));

            if ($idEquipo !== null) {
                $stmtUpsert->execute([
                    $idPartido, 
                    (int)$idUsuario, 
                    $idEquipo, 
                    $goles, 
                    $asistencias, 
                    $amarillas, 
                    $rojas
                ]);
            }
        }

        $db->commit();
        $_SESSION['flash_success'] = "¡Planilla de estadísticas actualizada correctamente!";
    } catch (\Exception $e) {
        $db->rollBack();
        $_SESSION['flash_error'] = "Ocurrió un error al guardar las estadísticas: " . $e->getMessage();
    }

    return $response->withHeader('Location', "/torneos/{$partidoInfo['id_torneo']}/fixture")->withStatus(302);
})->add(AuthMiddleware::class);

// Tabla de Estadísticas Generales del Torneo
$app->get("/torneos/{id}/estadisticas", function ($request, $response, $args) use ($renderer) {
    $idTorneo = $args['id'];

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $stmtTorneo = $db->prepare("SELECT * FROM torneos WHERE id = ?");
    $stmtTorneo->execute([$idTorneo]);
    $torneo = $stmtTorneo->fetch(PDO::FETCH_ASSOC);

    if (!$torneo) {
        $response->getBody()->write("Torneo no encontrado.");
        return $response->withStatus(404);
    }

    $sqlStats = "SELECT 
                    u.id AS id_usuario,
                    u.nombre AS jugador_nombre,
                    e.nombre AS equipo_nombre,
                    COUNT(pjs.id) AS pj,
                    SUM(CASE 
                        WHEN (p.id_equipo_local = pjs.id_equipo AND p.goles_local > p.goles_visitante) OR 
                             (p.id_equipo_visitante = pjs.id_equipo AND p.goles_visitante > p.goles_local) 
                        THEN 1 ELSE 0 
                    END) AS pg,
                    SUM(CASE 
                        WHEN (p.id_equipo_local = pjs.id_equipo AND p.goles_local < p.goles_visitante) OR 
                             (p.id_equipo_visitante = pjs.id_equipo AND p.goles_visitante < p.goles_local) 
                        THEN 1 ELSE 0 
                    END) AS pp,
                    COALESCE(SUM(pjs.goles), 0) AS goles,
                    COALESCE(SUM(pjs.asistencias), 0) AS asistencias,
                    COALESCE(SUM(pjs.amarillas), 0) AS amarillas,
                    COALESCE(SUM(pjs.rojas), 0) AS rojas
                 FROM partido_jugador_stats pjs
                 JOIN partidos p ON pjs.id_partido = p.id
                 JOIN usuarios u ON pjs.id_usuario = u.id
                 JOIN equipos e ON pjs.id_equipo = e.id
                 WHERE p.id_torneo = ? AND p.estado = 'finalizado'
                 GROUP BY u.id, u.nombre, e.nombre
                 ORDER BY goles DESC, asistencias DESC, pj ASC";

    $stmtStats = $db->prepare($sqlStats);
    $stmtStats->execute([$idTorneo]);
    $estadisticas = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

    return view($renderer, $response, "torneos/estadisticas.php", [
        "torneo" => $torneo,
        "estadisticas" => $estadisticas
    ]);
})->add(AuthMiddleware::class);