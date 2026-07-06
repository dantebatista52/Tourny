<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Torneo - Tourny</title>
</head>
<body>

    <p><a href="/dashboard">← Volver al Dashboard</a></p>

    <h1>Crear un Nuevo Torneo</h1>

    <form action="/torneos/create" method="POST">
        <div>
            <label for="nombre">Nombre del Torneo:</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej: Torneo Relámpago 2026" required>
        </div>

        <div>
            <label for="formato">Formato del Torneo:</label>
            <select id="formato" name="formato" required>
                <option value="">-- Selecciona un formato --</option>
                <option value="liga">Liga (Todos contra todos)</option>
                <option value="eliminatoria">Eliminatoria Directa</option>
            </select>
        </div>

        <div>
            <button type="submit">Crear Torneo y Continuar →</button>
        </div>
    </form>

</body>
</html>