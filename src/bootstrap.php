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

// Ruta/Vista principal
$app->get("/", function ($request, $response) use ($renderer) {
  return view($renderer, $response, "index.php");
});

// auth

$app->get("/login", function ($request, $response) use ($renderer) {
return view($renderer, $response, "/auth/login.php");
});

$app->get("/registro", function ($request, $response) use ($renderer) {
return view($renderer, $response, "/auth/registro.php");
});

// dashboard

$app->get("/dashboard", function ($request, $response) use ($renderer) {
return view($renderer, $response, "/dashboard/index.php");
});

// 

$app->addErrorMiddleware($debug, true, true);

return $app;
