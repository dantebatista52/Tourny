<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;
use Database;

class HomeController
{
    private PhpRenderer $renderer;

    public function __construct(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function landing(Request $request, Response $response): Response
    {
        return viewStandalone($this->renderer, $response, "landing.php", [
            "titulo" => "Bienvenido a Tourny",
            "isLoggedIn" => isset($_SESSION['usuario_id'])
        ]);
    }

    public function verTorneoPublico(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'];
        $databaseInstancia = new Database();
        $db = $databaseInstancia->getConnection();

        $stmtTorneo = $db->prepare("SELECT * FROM torneos WHERE slug = ?");
        $stmtTorneo->execute([$slug]);
        $torneo = $stmtTorneo->fetch();

        if (!$torneo) {
            $response->getBody()->write("Torneo no encontrado.");
            return $response->withStatus(404);
        }

        $idTorneo = $torneo['id'];

        $sqlPartidos = "SELECT p.*, 
                               el.nombre AS local_nombre, 
                               ev.nombre AS visitante_nombre 
                        FROM partidos p
                        LEFT JOIN equipos el ON p.id_equipo_local = el.id
                        LEFT JOIN equipos ev ON p.id_equipo_visitante = ev.id
                        WHERE p.id_torneo = ?
                        ORDER BY p.fecha_numero ASC, p.id ASC";
        $stmtPartidos = $db->prepare($sqlPartidos);
        $stmtPartidos->execute([$idTorneo]);
        $partidos = $stmtPartidos->fetchAll();

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

        foreach ($partidos as $p) {
            if ($p['goles_local'] !== null && $p['goles_visitante'] !== null) {
                $idLocal = $p['id_equipo_local'];
                $idVisitante = $p['id_equipo_visitante'];

                if (isset($tabla[$idLocal]) && isset($tabla[$idVisitante])) {
                    $golesL = (int)$p['goles_local'];
                    $golesV = (int)$p['goles_visitante'];

                    $tabla[$idLocal]['pj']++;
                    $tabla[$idVisitante]['pj']++;
                    $tabla[$idLocal]['gf'] += $golesL;
                    $tabla[$idLocal]['gc'] += $golesV;
                    $tabla[$idVisitante]['gf'] += $golesV;
                    $tabla[$idVisitante]['gc'] += $golesL;

                    if ($golesL > $golesV) {
                        $tabla[$idLocal]['pg']++; $tabla[$idLocal]['pts'] += 3; $tabla[$idVisitante]['pp']++;
                    } elseif ($golesV > $golesL) {
                        $tabla[$idVisitante]['pg']++; $tabla[$idVisitante]['pts'] += 3; $tabla[$idLocal]['pp']++;
                    } else {
                        $tabla[$idLocal]['pe']++; $tabla[$idLocal]['pts'] += 1;
                        $tabla[$idVisitante]['pe']++; $tabla[$idVisitante]['pts'] += 1;
                    }
                }
            }
        }

        foreach ($tabla as &$e) { $e['dg'] = $e['gf'] - $e['gc']; }
        unset($e);

        usort($tabla, function ($a, $b) {
            if ($b['pts'] !== $a['pts']) return $b['pts'] <=> $a['pts'];
            if ($b['dg'] !== $a['dg']) return $b['dg'] <=> $a['dg'];
            return $b['gf'] <=> $a['gf'];
        });

        return view($this->renderer, $response, "public/torneo_slug.php", [
            "torneo" => $torneo,
            "partidos" => $partidos,
            "tabla" => $tabla
        ]);
    }
}