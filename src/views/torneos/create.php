<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Torneo</title>
</head>
<body>

    <h1>Crear Nuevo Torneo</h1>
    
    <form action="/torneos/create" method="POST">
        
        <div>
            <label for="nombre">Nombre del Torneo:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>

        <div>
            <label for="tipo">Formato de Competición:</label>
            <select id="tipo" name="tipo">
                <option value="liga">Liga</option>
                <option value="eliminatoria">Eliminatoria directa</option>
            </select>
        </div>

        <div>
            <button type="submit">Crear Torneo</button>
        </div>

    </form>

</body>
</html>