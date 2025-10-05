<?php

/**
 * dashboard.php
 *
 * Página principal que se muestra tras la autenticación.
 * Requisitos:
 * - Debe existir una sesión válida con $_SESSION['user_id'] y $_SESSION['user_name'].
 *
 * Funcionalidad:
 * - Muestra un saludo personalizado: "BIENVENIDO, {nombre}" y un menú horizontal
 *   con enlaces a las diferentes secciones de la aplicación.
 */

session_start();

// Si no hay sesión válida, redirigir al login
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$userName = $_SESSION['user_name'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ALMACÉN DE BICICLETAS LA GRAN RUTA</title>
  <link rel="stylesheet" href="css/styles.css" />
</head>

<body>
  <div class="container">
    <header class="header">
      <div class="header-row">
        <div class="header-brand">
          <img src="images/logo.png" alt="Logo La Gran Ruta" class="logo" />
          <h1 class="title">LA GRAN RUTA</h1>
        </div>
        <nav class="top-nav" aria-label="Menú principal">
          <a href="inventario.php" class="menu-button">Inventario</a>
          <a href="nomina.php" class="menu-button">Nómina</a>
          <a href="mantenimiento.php" class="menu-button">Mantenimiento</a>
          <a href="ventas.php" class="menu-button">Ventas</a>
          <a href="logout.php" class="menu-button">Cerrar sesión</a>
        </nav>
      </div>
    </header>

    <main class="main-content">
      <h2 class="welcome">BIENVENIDO, <?php echo htmlspecialchars($userName); ?></h2>
      <p class="instruction">Seleccione una función del menú para empezar</p>

      <!-- Menú principal reemplazado por el menú horizontal en el header -->
      <div class="spacer-small"></div>
    </main>

    <footer class="footer">
      <p class="rights">Todos los derechos reservados</p>
    </footer>
  </div>
  <script>
    // Marca el enlace activo en el top-nav comparando la ruta
    (function() {
      try {
        var links = document.querySelectorAll('.top-nav a');
        var path = window.location.pathname.split('/').pop();
        links.forEach(function(a) {
          var href = a.getAttribute('href');
          if (!href) return;
          var hpage = href.split('/').pop();
          if (hpage === path) {
            a.classList.add('active');
          }
        });
      } catch (e) {
        console && console.warn && console.warn(e)
      }
    })();
  </script>
</body>

</html>