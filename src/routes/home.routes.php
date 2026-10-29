<?php

use Slim\App;
use Slim\Views\PhpRenderer;
use App\Controllers\HomeController;
use App\Middlewares\GuestMiddleware;

/** @var App $app */
/** @var PhpRenderer $renderer */

$homeController = new HomeController($renderer);

$app->get("/", [$homeController, 'landing'])->add(GuestMiddleware::class);
$app->get("/torneo/{slug}", [$homeController, 'verTorneoPublico'])->add(GuestMiddleware::class);