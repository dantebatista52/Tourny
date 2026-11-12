<?php

use Slim\App;
use Slim\Views\PhpRenderer;
use App\Middlewares\AuthMiddleware;

/** @var App $app */
/** @var PhpRenderer $renderer */
/** @var Database $database */

// ==========================================
// 3. DASHBOARD PRINCIPAL
// ==========================================

$app->get("/dashboard", function ($request, $response) use ($renderer) {
    $idUsuario = $_SESSION['usuario_id'];

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    // 1. Obtener los torneos administrados como organizador
    $sqlTorneos = "SELECT 
                    t.*, 
                    COUNT(e.id) AS cantidad_equipos 
                FROM torneos t 
                LEFT JOIN equipos e ON e.id_torneo = t.id 
                WHERE t.id_organizador = ? 
                GROUP BY t.id 
                ORDER BY t.created_at DESC";

    $stmtTorneos = $db->prepare($sqlTorneos);
    $stmtTorneos->execute([$idUsuario]);
    $torneos = $stmtTorneos->fetchAll();

    // 2. Obtener los equipos donde el usuario está registrado (ordenado por fecha_union)
    $sqlMisEquipos = "SELECT 
                        e.id, 
                        e.nombre, 
                        t.nombre AS torneo_nombre
                      FROM equipo_jugadores ej
                      JOIN equipos e ON ej.id_equipo = e.id
                      JOIN torneos t ON e.id_torneo = t.id
                      WHERE ej.id_usuario = ?
                      ORDER BY ej.fecha_union DESC";

    $stmtMisEquipos = $db->prepare($sqlMisEquipos);
    $stmtMisEquipos->execute([$idUsuario]);
    $misEquipos = $stmtMisEquipos->fetchAll();

    return view($renderer, $response, "dashboard/dashboard.php", [
        "nombre" => $_SESSION['usuario_nombre'],
        "torneos" => $torneos,
        "misEquipos" => $misEquipos
    ]);
})->add(AuthMiddleware::class);

// ==========================================
// 4. TORNEOS (ZONA PRIVADA)
// ==========================================

$app->get("/torneos", function ($request, $response) use ($renderer) {
    $idOrganizador = $_SESSION['usuario_id'];

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $stmt = $db->prepare("SELECT * FROM torneos WHERE id_organizador = ? ORDER BY created_at DESC");
    $stmt->execute([$idOrganizador]);
    $torneos = $stmt->fetchAll();

    return view($renderer, $response, "torneos/index.php", [
        "torneos" => $torneos
    ]);
})->add(AuthMiddleware::class);

$app->get("/torneos/create", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "torneos/create.php");
})->add(AuthMiddleware::class);

// ==========================================
// CREAR TORNEO (Con Cupo Máximo)
// ==========================================
$app->post("/torneos/create", function ($request, $response) use ($renderer) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['usuario_id'])) {
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    $parsedBody = $request->getParsedBody();
    $nombreTorneo = trim($parsedBody['nombre'] ?? '');
    $formato = $parsedBody['formato'] ?? 'liga';
    $cupoMaximo = !empty($parsedBody['cupo_maximo']) ? (int)$parsedBody['cupo_maximo'] : 16;
    $idOrganizador = $_SESSION['usuario_id'];

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombreTorneo), '-'));

    if (empty($nombreTorneo) || empty($formato) || empty($slug) || $cupoMaximo < 2) {
        $response->getBody()->write("Todos los campos son obligatorios y el cupo debe ser de al menos 2 equipos.");
        return $response->withStatus(400);
    }

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $stmt = $db->prepare("INSERT INTO torneos (nombre, slug, formato, cupo_maximo, id_organizador) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nombreTorneo, $slug, $formato, $cupoMaximo, $idOrganizador]);

    $idTorneo = $db->lastInsertId();

    return $response->withHeader('Location', "/torneos/{$idTorneo}/equipos")->withStatus(302);
})->add(AuthMiddleware::class);

$app->post("/torneos/{id}/delete", function ($request, $response, $args) use ($renderer) {

    $idTorneo = $args['id'];
    $idOrganizador = $_SESSION['usuario_id'];

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    $stmt = $db->prepare("SELECT id FROM torneos WHERE id = ? AND id_organizador = ?");
    $stmt->execute([$idTorneo, $idOrganizador]);
    
    if (!$stmt->fetch()) {
        $response->getBody()->write("No tienes permisos para eliminar este torneo o no existe.");
        return $response->withStatus(403);
    }

    $stmtDelete = $db->prepare("DELETE FROM torneos WHERE id = ?");
    $stmtDelete->execute([$idTorneo]);

    return $response->withHeader('Location', '/dashboard')->withStatus(302);
})->add(AuthMiddleware::class);