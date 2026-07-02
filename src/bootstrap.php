<?php

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

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

// Crear el motor de plantillas
$renderer = new PhpRenderer(
  templatePath: __DIR__ . "/views",
  attributes: ["title" => "Tourny - Gestor de torneos personalizados"],
);

// ==========================================
// 1. RUTA PÚBLICA / LANDING
// ==========================================
$app->get("/", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "public/landing.php"); // Cambiado a public/landing según tu flujo
});

// Vista pública compartida para los jugadores (Solo lectura)
$app->get("/torneo/{slug}", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "public/torneo_slug.php");
});

// ==========================================
// 2. AUTENTICACIÓN (AUTH)
// ==========================================
$app->get("/login", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "auth/login.php");
});

$app->get("/registro", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "auth/registro.php");
});

// ==========================================
// 3. DASHBOARD PRINCIPAL
// ==========================================
$app->get("/dashboard", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "dashboard/index.php");
});

// ==========================================
// 4. TORNEOS (ZONA PRIVADA)
// ==========================================

// Acceder formulario para crear un torneo
$app->get("/torneos/create", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "torneos/create.php");
});

// Crear torneo con metodo post

$app->post("/torneos/create", function ($request, $response) use ($renderer) {
    // Captura los datos enviados por el formulario POST
    $parsedBody = $request->getParsedBody();
    
    // Obtenemos lo que el usuario ingresó (con valores por defecto por si vienen vacíos)
    $nombreTorneo = $parsedBody['nombre'] ?? 'Torneo de Prueba';
    $tipoTorneo = $parsedBody['tipo'] ?? 'liga';

    // Para la prueba, enviamos estos datos directamente a la vista 'detail.php'
    return view($renderer, $response, "torneos/detail.php", [
        "nombre" => $nombreTorneo,
        "tipo" => $tipoTorneo,
        "id" => 1 // ID simulado
    ]);
});

// Detalle general de un torneo específico
$app->get("/torneos/{id}", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "torneos/detail.php");
});

// Gestión de equipos inscritos en el torneo
$app->get("/torneos/{id}/equipos", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "torneos/equipos.php");
});

// Tabla de posiciones o Brackets del torneo
$app->get("/torneos/{id}/tabla", function ($request, $response) use ($renderer) {
    return view($renderer, $response, "torneos/tabla.php");
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
