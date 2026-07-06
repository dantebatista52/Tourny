<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Tourny</title>
</head>
<body>

    <h1>¡Bienvenido, <?php echo htmlspecialchars($nombre); ?>!</h1>
    <p>Este es tu panel de control de Tourny.</p>

    <hr>

    <h2>Tus Acciones</h2>
    <ul>
        <li><a href="/torneos/create">Crear un Nuevo Torneo</a></li>
        <li><a href="/torneos">Ver mis Torneos creados</a></li>
    </ul>

    <hr>

    <p><a href="/logout">Cerrar Sesión</a></p>

</body>
</html>