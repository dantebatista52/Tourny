<?php

use Slim\App;
use Slim\Views\PhpRenderer;
use App\Middlewares\AuthMiddleware;

/** @var App $app */
/** @var PhpRenderer $renderer */
/** @var Database $database */

// ==========================================
// GESTIÓN DE EQUIPOS (ZONA PRIVADA)
// ==========================================

$app->get("/torneos/{id}/equipos", function ($request, $response, $args) use ($renderer) {
    $idTorneo = $args['id'];

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $stmt = $db->prepare("SELECT * FROM torneos WHERE id = ?");
    $stmt->execute([$idTorneo]);
    $torneo = $stmt->fetch();

    if (!$torneo) {
        $response->getBody()->write("El torneo no existe.");
        return $response->withStatus(404);
    }

    $stmt = $db->prepare("SELECT * FROM equipos WHERE id_torneo = ?");
    $stmt->execute([$idTorneo]);
    $equipos = $stmt->fetchAll();

    // 🟢 Verificar si ya existen partidos generados para este torneo
    $stmtFixture = $db->prepare("SELECT COUNT(*) FROM partidos WHERE id_torneo = ?");
    $stmtFixture->execute([$idTorneo]);
    $tieneFixture = ($stmtFixture->fetchColumn() > 0);

    return view($renderer, $response, "torneos/equipos.php", [
        "torneo" => $torneo,
        "equipos" => $equipos,
        "tieneFixture" => $tieneFixture
    ]);
})->add(AuthMiddleware::class);

// ==========================================
// CREAR EQUIPO EN TORNEO (Con Validación de Cupo)
// ==========================================
$app->post("/torneos/{id}/equipos", function ($request, $response, $args) {
    $idTorneo = $args['id'];
    $parsedBody = $request->getParsedBody();
    $nombreEquipo = trim($parsedBody['nombre_equipo'] ?? '');

    if (empty($nombreEquipo)) {
        $response->getBody()->write("El nombre del equipo no puede estar vacío.");
        return $response->withStatus(400);
    }

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    // 1. Obtener cupo máximo y conteo actual de equipos
    $stmtTorneo = $db->prepare("SELECT cupo_maximo FROM torneos WHERE id = ?");
    $stmtTorneo->execute([$idTorneo]);
    $torneo = $stmtTorneo->fetch();

    if (!$torneo) {
        $response->getBody()->write("El torneo no existe.");
        return $response->withStatus(404);
    }

    $stmtCount = $db->prepare("SELECT COUNT(*) FROM equipos WHERE id_torneo = ?");
    $stmtCount->execute([$idTorneo]);
    $totalEquipos = (int)$stmtCount->fetchColumn();

    // 🔴 VALIDACIÓN DE CUPO MÁXIMO
    if ($totalEquipos >= (int)$torneo['cupo_maximo']) {
        $response->getBody()->write("No se pueden registrar más equipos. El torneo ha alcanzado su cupo máximo de {$torneo['cupo_maximo']} equipos.");
        return $response->withStatus(400);
    }

    // 2. Validar nombre duplicado
    $stmtCheck = $db->prepare("SELECT id FROM equipos WHERE id_torneo = ? AND LOWER(nombre) = LOWER(?)");
    $stmtCheck->execute([$idTorneo, $nombreEquipo]);

    if ($stmtCheck->fetch()) {
        $response->getBody()->write("Ya existe un equipo con el nombre '{$nombreEquipo}' en este torneo.");
        return $response->withStatus(400);
    }

    $codigoInvitacion = generarCodigoInvitacion(6);

    $stmt = $db->prepare("INSERT INTO equipos (id_torneo, nombre, codigo_invitacion) VALUES (?, ?, ?)");
    $stmt->execute([$idTorneo, $nombreEquipo, $codigoInvitacion]);

    return $response->withHeader('Location', "/torneos/{$idTorneo}/equipos")->withStatus(302);
})->add(AuthMiddleware::class);

// 🟢 VISTA PARA UNIRSE A UN EQUIPO (GET)
$app->get("/equipos/unirse", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "equipos/unirse.php");
})->add(AuthMiddleware::class);

// 🟢 UNIRSE A UN EQUIPO (Con validación de equipo único por torneo) (POST)
$app->post("/equipos/unirse", function ($request, $response) {
    $idUsuario = $_SESSION['usuario_id'] ?? null;

    if (!$idUsuario) {
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    $parsedBody = $request->getParsedBody();
    $codigo = strtoupper(trim($parsedBody['codigo_invitacion'] ?? ''));

    if (empty($codigo)) {
        $_SESSION['flash_error'] = "Debes ingresar un código de invitación.";
        return $response->withHeader('Location', '/equipos/unirse')->withStatus(302);
    }

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    // 1. Obtener datos del equipo por su código
    $stmt = $db->prepare("SELECT id, nombre, id_torneo FROM equipos WHERE codigo_invitacion = ?");
    $stmt->execute([$codigo]);
    $equipo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$equipo) {
        $_SESSION['flash_error'] = "El código ingresado no es válido o venció.";
        return $response->withHeader('Location', '/equipos/unirse')->withStatus(302);
    }

    // 2. RESTRICCIÓN: Verificar si el usuario ya está en OTRO equipo del MISMO torneo
    $sqlTorneoCheck = "SELECT e.nombre AS equipo_nombre, e.id AS equipo_id 
                       FROM equipo_jugadores ej
                       JOIN equipos e ON ej.id_equipo = e.id
                       WHERE e.id_torneo = ? AND ej.id_usuario = ?";
    $stmtTorneoCheck = $db->prepare($sqlTorneoCheck);
    $stmtTorneoCheck->execute([$equipo['id_torneo'], $idUsuario]);
    $equipoExistente = $stmtTorneoCheck->fetch(PDO::FETCH_ASSOC);

    if ($equipoExistente) {
        if ($equipoExistente['equipo_id'] == $equipo['id']) {
            $_SESSION['flash_error'] = "Ya perteneces a este equipo.";
        } else {
            $_SESSION['flash_error'] = "Ya estás registrado en '" . htmlspecialchars($equipoExistente['equipo_nombre']) . "' para este mismo torneo. Debes salir de ese equipo antes de unirte a otro.";
        }
        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    // 3. Registrar al usuario en el equipo
    $stmtInsert = $db->prepare("INSERT INTO equipo_jugadores (id_equipo, id_usuario) VALUES (?, ?)");
    $stmtInsert->execute([$equipo['id'], $idUsuario]);

    $_SESSION['flash_success'] = "¡Te has unido con éxito a " . htmlspecialchars($equipo['nombre']) . "!";
    return $response->withHeader('Location', "/equipos/" . $equipo['id'] . "/mi-equipo")->withStatus(302);
})->add(AuthMiddleware::class);

// VER LA PLANTILLA DEL EQUIPO, ESTADÍSTICAS Y EL FORMULARIO
$app->get("/equipos/{id}/mi-equipo", function ($request,$response, $args) use ($renderer) {
    $idEquipo =$args['id'];
    $idUsuario =$_SESSION['usuario_id'] ?? null;

    $databaseInstancia = new Database();
    $db =$databaseInstancia->getConnection();

    // Obtener datos del equipo y del torneo al que pertenece
    $stmtEquipo =$db->prepare("SELECT e.*, t.nombre AS torneo_nombre, t.id AS torneo_id FROM equipos e JOIN torneos t ON e.id_torneo = t.id WHERE e.id = ?");
    $stmtEquipo->execute([$idEquipo]);
    $equipo =$stmtEquipo->fetch();

    if (!$equipo) {$response->getBody()->write("Equipo no encontrado.");
        return $response->withStatus(404);
    }

    // Obtener jugadores del equipo con sus estadísticas acumuladas
    $sqlJugadores = "SELECT 
                        u.id AS id_usuario,
                        u.nombre,
                        ej.dorsal,
                        ej.posicion,
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
                     FROM equipo_jugadores ej
                     JOIN usuarios u ON ej.id_usuario = u.id
                     LEFT JOIN partido_jugador_stats pjs ON ej.id_usuario = pjs.id_usuario AND ej.id_equipo = pjs.id_equipo
                     LEFT JOIN partidos p ON pjs.id_partido = p.id AND p.estado = 'finalizado'
                     WHERE ej.id_equipo = ?
                     GROUP BY u.id, u.nombre, ej.dorsal, ej.posicion
                     ORDER BY goles DESC, asistencias DESC, ej.dorsal ASC";
    $stmtJugadores = $db->prepare($sqlJugadores);
    $stmtJugadores->execute([$idEquipo]);
    $jugadores =$stmtJugadores->fetchAll();

    $stmtMiFichaje =$db->prepare("SELECT * FROM equipo_jugadores WHERE id_equipo = ? AND id_usuario = ?");
    $stmtMiFichaje->execute([$idEquipo,$idUsuario]);
    $miFichaje =$stmtMiFichaje->fetch();

    return view($renderer,$response, "equipos/mi_equipo.php", [
        "equipo" => $equipo,
        "jugadores" => $jugadores,
        "miFichaje" => $miFichaje
    ]);
})->add(AuthMiddleware::class);

// ACTUALIZAR PERFIL (DORSAL Y POSICIÓN)
$app->post("/equipos/{id}/mi-equipo/perfil", function ($request, $response, $args) {
    $idEquipo = $args['id'];
    $idUsuario = $_SESSION['usuario_id'];
    $parsedBody = $request->getParsedBody();

    $dorsal = !empty($parsedBody['dorsal']) ? (int)$parsedBody['dorsal'] : null;
    $posicion = trim($parsedBody['posicion'] ?? '');

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $stmt = $db->prepare("UPDATE equipo_jugadores SET dorsal = ?, posicion = ? WHERE id_equipo = ? AND id_usuario = ?");
    $stmt->execute([$dorsal, $posicion, $idEquipo, $idUsuario]);

    $_SESSION['flash_success'] = "¡Tus datos de jugador fueron actualizados correctamente!";
    return $response->withHeader('Location', "/equipos/{$idEquipo}/mi-equipo")->withStatus(302);
})->add(AuthMiddleware::class);

// 🟢 SALIR DEL EQUIPO
$app->post("/equipos/{id}/salir", function ($request, $response, $args) {
    $idEquipo = $args['id'];
    $idUsuario = $_SESSION['usuario_id'];

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $stmtDelete = $db->prepare("DELETE FROM equipo_jugadores WHERE id_equipo = ? AND id_usuario = ?");
    $stmtDelete->execute([$idEquipo, $idUsuario]);

    $_SESSION['flash_success'] = "Has salido del equipo correctamente.";
    return $response->withHeader('Location', '/dashboard')->withStatus(302);
})->add(AuthMiddleware::class);