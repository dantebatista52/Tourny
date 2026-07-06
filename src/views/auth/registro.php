<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Tourny</title>
</head>
<body>

    <h1>Registro de Organizador</h1>

    <form action="/registro" method="POST">
        <div>
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>

        <div>
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div>
            <button type="submit">Registrarse</button>
        </div>
    </form>

    <p>¿Ya tenés cuenta? <a href="/login">Iniciá sesión acá</a></p>

</body>
</html>