<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Equipos</title>
</head>
<body>

    <p><a href="/torneos/1">← Volver al detalle del torneo</a></p>

    <h1>Gestionar Equipos - Liga de Prueba</h1>

    <h2>Añadir Nuevo Equipo</h2>
    <form action="/torneos/1/equipos" method="POST">
        <div>
            <label for="nombre_equipo">Nombre del Equipo:</label>
            <input type="text" id="nombre_equipo" name="nombre_equipo" placeholder="Ej: Deportivo FC" required>
            <button type="submit">Agregar</button>
        </div>
    </form>

</body>