<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Torneo</title>
</head>
<body>

    <p>¡Torneo Creado con Éxito (Prueba POST)!</p>
    
    <h1><?php echo htmlspecialchars($nombre); ?></h1>
    
    <ul>
        <li><strong>ID del Torneo:</strong> #<?php echo $id; ?></li>
        <li><strong>Formato:</strong> <?php echo htmlspecialchars($tipo); ?></li>
    </ul>

    <div>
        <a href="/torneos/create">Volver al formulario</a>
    </div>

</body>
</html>