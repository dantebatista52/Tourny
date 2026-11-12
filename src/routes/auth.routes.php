<?php

use Slim\App;
use Slim\Views\PhpRenderer;
use App\Controllers\AuthController;
use App\Middlewares\GuestMiddleware;

/** @var App $app */
/** @var PhpRenderer $renderer */

$authController = new AuthController($renderer);

$app->get("/registro", [$authController, 'showRegistro'])->add(GuestMiddleware::class);
$app->post("/registro", [$authController, 'processRegistro'])->add(GuestMiddleware::class);

$app->get("/login", [$authController, 'showLogin'])->add(GuestMiddleware::class);
$app->post("/login", [$authController, 'processLogin'])->add(GuestMiddleware::class);

$app->map(['GET', 'POST'], '/logout', [$authController, 'logout']);