<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;
use Database;

class AuthController
{
    private PhpRenderer $renderer;

    public function __construct(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function showRegistro(Request $request, Response $response): Response
    {
        return view($this->renderer, $response, "auth/registro.php");
    }

    public function processRegistro(Request $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $nombre = $parsedBody['nombre'] ?? '';
        $email = $parsedBody['email'] ?? '';
        $password = $parsedBody['password'] ?? '';

        if (empty($nombre) || empty($email) || empty($password)) {
            $response->getBody()->write("Todos los campos son obligatorios.");
            return $response->withStatus(400);
        }

        $databaseInstancia = new Database();
        $db = $databaseInstancia->getConnection();

        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $response->getBody()->write("El email ya está registrado.");
            return $response->withStatus(400);
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $email, $passwordHash]);

        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    public function showLogin(Request $request, Response $response): Response
    {
        return view($this->renderer, $response, "auth/login.php");
    }

    public function processLogin(Request $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $email = $parsedBody['email'] ?? '';
        $password = $parsedBody['password'] ?? '';

        if (empty($email) || empty($password)) {
            $response->getBody()->write("Por favor, completa todos los campos.");
            return $response->withStatus(400);
        }

        $databaseInstancia = new Database();
        $db = $databaseInstancia->getConnection();

        $stmt = $db->prepare("SELECT id, nombre, password_hash FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            $response->getBody()->write("Credenciales incorrectas.");
            return $response->withStatus(401);
        }

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];

        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    public function logout(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();

        return $response->withHeader('Location', '/')->withStatus(302);
    }
}