<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Tourny</title>
</head>
<body>

    <h1>Iniciar Sesión</h1>

    <form action="/login" method="POST">
        <div>
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div>
            <button type="submit">Ingresar</button>
        </div>
    </form>

    <p>¿No tenés cuenta? <a href="/registro">Registrate acá</a></p>

</body>
</html>