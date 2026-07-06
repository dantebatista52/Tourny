<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Equipos - Tourny</title>
</head>
<body>

    <p><a href="/dashboard">← Volver al Dashboard</a></p>

    <h1>Gestionar Equipos - <?php echo htmlspecialchars($torneo['nombre']); ?></h1>
    <p>Formato seleccionado: <strong><?php echo htmlspecialchars($torneo['formato']); ?></strong></p>

    <h2>Añadir Nuevo Equipo</h2>
    <form action="/torneos/<?php echo $torneo['id']; ?>/equipos" method="POST">
        <div>
            <label for="nombre_equipo">Nombre del Equipo:</label>
            <input type="text" id="nombre_equipo" name="nombre_equipo" placeholder="Ej: Boca Juniors" required>
            <button type="submit">Agregar</button>
        </div>
    </form>

    <hr>

    <h2>Equipos Registrados (<?php echo count($equipos); ?>)</h2>
    
    <?php if (empty($equipos)): ?>
        <p>Todavía no hay equipos anotados en este torneo.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($equipos as $equipo): ?>
                <li>
                    <span><?php echo htmlspecialchars($equipo['nombre']); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <hr>

    <div>
        <a href="/torneos/<?php echo $torneo['id']; ?>/fixture">
            <button>Generar Fixture / Calendario →</button>
        </a>
    </div>

</body>
</html>