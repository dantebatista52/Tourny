<?php

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Dotenv\Dotenv;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\GuestMiddleware;

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

// Crear el motor de plantillas
$renderer = new PhpRenderer(
  templatePath: __DIR__ . "/views",
  attributes: ["title" => "Tourny - Gestor de torneos personalizados"],
);

// HELPER: Generador de código de invitación de 6 caracteres
function generarCodigoInvitacion(int $longitud = 6): string {
    $caracteres = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $max = strlen($caracteres) - 1;
    $codigo = '';
    for ($i = 0; $i < $longitud; $i++) {
        $codigo .= $caracteres[random_int(0, $max)];
    }
    return $codigo;
}

// CARGAR ARCHIVOS DE RUTAS (Incluyendo partidos.routes.php)
require __DIR__ . '/routes/auth.routes.php';
require __DIR__ . '/routes/torneos.routes.php';
require __DIR__ . '/routes/equipos.routes.php';
require __DIR__ . '/routes/home.routes.php';
require __DIR__ . '/routes/partidos.routes.php'; // 🟢 ¡ESTE ERA EL FALTANTE!

// Activar el middleware nativo de errores de Slim con detalles completos para depuración
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

/* 
  Comentamos temporalmente el manejador personalizado para que Slim 
  muestre el error exacto (SQL, línea de código, etc.) en pantalla 
  si ocurre algún inconveniente.
*/
// $customErrorHandler = require __DIR__ . '/middlewares/errorMiddleware.php';
// $errorMiddleware->setDefaultErrorHandler($customErrorHandler);

return $app;