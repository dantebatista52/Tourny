<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechConsult - Soluciones en Programación</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <!-- NAVEGACIÓN -->
  <header>
    <nav>
      <div class="logo">TechConsult</div>
      <ul>
        <li><a href="#servicios">Servicios</a></li>
        <li><a href="#nosotros">Nosotros</a></li>
        <li><a href="#contacto">Contacto</a></li>
        <!-- En PHP esto será: <a href="servicios.php"> -->
      </ul>
    </nav>
  </header>

  <main>

    <!-- SECCIÓN HERO: único H1 -->
    <section id="hero">
      <h1>Quadruplex: Soluciones tecnológicas a medida</h1>
      <p>Somos una consultora especializada en desarrollo de software y programación.</p>
      <a href="#contacto" class="btn-cta">Contactanos</a>
    </section>

    <!-- SERVICIOS -->
    <section id="servicios">
      <h2>Nuestros Servicios</h2>
      <div class="cards">

        <article class="card">
          <h3>Desarrollo Web</h3>
          <p>Creamos sitios y aplicaciones web con tecnologías modernas.</p>
        </article>

        <article class="card">
          <h3>Bases de Datos</h3>
          <p>Diseño y administración de bases relacionales y no relacionales.</p>
        </article>

        <article class="card">
          <h3>Automatización</h3>
          <p>Scripts y herramientas para optimizar procesos repetitivos.</p>
        </article>

      </div>
    </section>

    <!-- EQUIPO -->
    <section id="nosotros">
      <h2>Nuestro Equipo</h2>
      <div class="team">

        <article class="miembro">
          <h3>Nombre Apellido</h3>
          <h4>Desarrollador Frontend</h4>
          <p>Descripción breve del integrante.</p>
        </article>

        <!-- Repetir para los 4 integrantes -->

      </div>
    </section>

    <!-- CONTACTO -->
    <section id="contacto">
      <h2>Contacto</h2>
      <!-- Acá irá el formulario que luego procesarás con PHP -->
      <form action="procesar_contacto.php" method="POST">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="mensaje">Mensaje</label>
        <textarea id="mensaje" name="mensaje" required></textarea>

        <button type="submit">Enviar</button>
      </form>
    </section>

  </main>

  <footer>
    <p>&copy; 2026 Quadruplex. Todos los derechos reservados.</p>
  </footer>

</body>
</html>
