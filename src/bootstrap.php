<?php

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/database/database.php';

// Cargar variables de entorno desde el .env
Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$env = $_ENV["APP_ENV"] ?? "prod";
$allowedEnvs = ["dev", "prod"];

if (!in_array($env, $allowedEnvs, true)) {
  throw new RuntimeException("APP_ENV inválido: $env");
}

$debug = $env === "dev";

// Crear la aplicacion de Slim
$app = AppFactory::create();
// Activa los errores detallados en el navegador
$app->addErrorMiddleware(true, true, true);

// Crear el motor de plantillas
$renderer = new PhpRenderer(
  templatePath: __DIR__ . "/views",
  attributes: ["title" => "Tourny - Gestor de torneos personalizados"],
);

// ==========================================
// 1. RUTA PÚBLICA / LANDING
// ==========================================

// Ruta de la Landing Page (GET /)
$app->get("/", function ($request, $response) use ($renderer) {
    // Iniciamos sesión para poder leer las variables
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verificamos si el usuario ya está logueado
    $isLoggedIn = isset($_SESSION['usuario_id']);

    // Le pasamos esa información a la vista landing.php
    return view($renderer, $response, "landing.php", [
        "isLoggedIn" => $isLoggedIn
    ]);
});

// Vista pública compartida para los jugadores (Solo lectura)
$app->get("/torneo/{slug}", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "public/torneo_slug.php");
});

// ==========================================
// 2. AUTENTICACIÓN (AUTH)
// ==========================================

// ==========================================
// RUTAS DE REGISTRO
// ==========================================

// Muestra el formulario de registro (GET)
$app->get("/registro", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "auth/registro.php");
});

// Procesa el formulario de registro (POST)
$app->post("/registro", function ($request, $response) use ($renderer) {
    $parsedBody = $request->getParsedBody();
    
    $nombre = $parsedBody['nombre'] ?? '';
    $email = $parsedBody['email'] ?? '';
    $password = $parsedBody['password'] ?? '';

    if (empty($nombre) || empty($email) || empty($password)) {
        $response->getBody()->write("Todos los campos son obligatorios.");
        return $response->withStatus(400);
    }

    // Instanciamos la conexión oficial del proyecto
    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    // Verificar si el email ya existe
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $response->getBody()->write("El email ya está registrado.");
        return $response->withStatus(400);
    }

    // Encriptar contraseña de manera segura
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Insertar en la base de datos
    $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $email, $passwordHash]);

    // Redireccionar al login tras registrarse con éxito
    return $response->withHeader('Location', '/login')->withStatus(302);
});


// ==========================================
// RUTAS DE LOGIN
// ==========================================

// Muestra el formulario de login (GET)
$app->get("/login", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "auth/login.php");
});

// Procesa el formulario de login (POST)
$app->post("/login", function ($request, $response) use ($renderer) {
    $parsedBody = $request->getParsedBody();
    
    $email = $parsedBody['email'] ?? '';
    $password = $parsedBody['password'] ?? '';

    if (empty($email) || empty($password)) {
        $response->getBody()->write("Por favor, completa todos los campos.");
        return $response->withStatus(400);
    }

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    // Buscar al usuario por email
    $stmt = $db->prepare("SELECT id, nombre, password_hash FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Verificar si existe y si la contraseña coincide con el hash guardado
    if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
        $response->getBody()->write("Credenciales incorrectas.");
        return $response->withStatus(401);
    }

    // SI TODO ESTÁ BIEN: Iniciamos la sesión de PHP
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Guardamos los datos clave del usuario en la sesión global
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];

    // Redireccionamos al dashboard (Panel de control del organizador)
    return $response->withHeader('Location', '/dashboard')->withStatus(302);
});

// ==========================================
// 3. DASHBOARD PRINCIPAL
// ==========================================

// Muestra el Panel de Control (GET)
$app->get("/dashboard", function ($request, $response) use ($renderer) {
    // Si no tiene la sesión iniciada, forzamos el inicio
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // PROTECCIÓN: Si no hay un usuario logueado, lo mandamos de patitas al login
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    // Si está logueado, le pasamos su nombre a la vista del dashboard
    return view($renderer, $response, "dashboard/dashboard.php", [
        "nombre" => $_SESSION['usuario_nombre']
    ]);
});

// Ruta para Cerrar Sesión (GET o POST, lo hacemos GET para probar fácil con un enlace)
$app->get("/logout", function ($request, $response) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Destruimos todas las variables de la sesión
    $_SESSION = [];
    session_destroy();

    // Lo mandamos al login de vuelta
    return $response->withHeader('Location', '/login')->withStatus(302);
});

// ==========================================
// 4. TORNEOS (ZONA PRIVADA)
// ==========================================

// Formulario para crear un torneo (GET)
$app->get("/torneos/create", function ($request, $response) use ($renderer) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // CONTROL DE ACCESO: Si no está logueado, al login
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    return view($renderer, $response, "torneos/create.php");
});

// Procesa la creación del torneo (POST)
$app->post("/torneos/create", function ($request, $response) use ($renderer) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // CONTROL DE ACCESO
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    $parsedBody = $request->getParsedBody();
    $nombreTorneo = $parsedBody['nombre'] ?? '';
    $formato = $parsedBody['formato'] ?? ''; // Ej: "liga", "eliminatoria"
    $idOrganizador = $_SESSION['usuario_id'];

    // Validación básica
    if (empty($nombreTorneo) || empty($formato)) {
        $response->getBody()->write("Todos los campos son obligatorios.");
        return $response->withStatus(400);
    }

    // Conexión usando la clase oficial del proyecto
    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    // Insertar el nuevo torneo
    $stmt = $db->prepare("INSERT INTO torneos (nombre, id_organizador, formato) VALUES (?, ?, ?)");
    $stmt->execute([$nombreTorneo, $idOrganizador, $formato]);

    // Obtenemos el ID del torneo que se acaba de crear para saber a dónde redirigir
    $idTorneo = $db->lastInsertId();

    // Redireccionamos directo a la gestión de equipos de ESTE torneo específico
    return $response->withHeader('Location', "/torneos/{$idTorneo}/equipos")->withStatus(302);
});

// ==========================================
// 4.5 GESTIÓN DE EQUIPOS (ZONA PRIVADA)
// ==========================================

// Muestra el panel de control de equipos para un torneo (GET)
$app->get("/torneos/{id}/equipos", function ($request, $response, $args) use ($renderer) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // CONTROL DE ACCESO: Si no está logueado, al login
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    $idTorneo = $args['id']; // Capturamos el ID del torneo desde la URL

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    // Verificamos que el torneo realmente exista
    $stmt = $db->prepare("SELECT * FROM torneos WHERE id = ?");
    $stmt->execute([$idTorneo]);
    $torneo = $stmt->fetch();

    if (!$torneo) {
        $response->getBody()->write("El torneo no existe.");
        return $response->withStatus(404);
    }

    // Traemos de la base de datos los equipos que ya pertenezcan a este torneo
    $stmt = $db->prepare("SELECT * FROM equipos WHERE id_torneo = ?");
    $stmt->execute([$idTorneo]);
    $equipos = $stmt->fetchAll();

    // Renderizamos la vista pasándole los datos dinámicos
    return view($renderer, $response, "torneos/equipos.php", [
        "torneo" => $torneo,
        "equipos" => $equipos
    ]);
});

// Procesa el formulario para añadir un nuevo equipo (POST)
$app->post("/torneos/{id}/equipos", function ($request, $response, $args) use ($renderer) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['usuario_id'])) {
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    $idTorneo = $args['id'];
    $parsedBody = $request->getParsedBody();
    $nombreEquipo = $parsedBody['nombre_equipo'] ?? '';

    if (empty($nombreEquipo)) {
        $response->getBody()->write("El nombre del equipo no puede estar vacío.");
        return $response->withStatus(400);
    }

    $databaseInstancia = new Database();
    $db = $databaseInstancia->getConnection();

    // Insertamos el equipo asociándolo al ID del torneo actual
    $stmt = $db->prepare("INSERT INTO equipos (id_torneo, nombre) VALUES (?, ?)");
    $stmt->execute([$idTorneo, $nombreEquipo]);

    // Recargamos la misma página por GET para ver el equipo reflejado en la lista
    return $response->withHeader('Location', "/torneos/{$idTorneo}/equipos")->withStatus(302);
});

// ==========================================
// 5. PARTIDOS / FIXTURE (ZONA PRIVADA)
// ==========================================

// Calendario/Fixture de un torneo
$app->get("/torneos/{id}/fixture", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "partidos/fixture.php");
});

// Cargar/Editar resultado de un partido específico
$app->get("/partidos/{id}/resultado", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "partidos/resultado.php");
});


$app->addErrorMiddleware($debug, true, true);

return $app;
