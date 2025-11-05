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
$current = basename($_SERVER['PHP_SELF']);
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
      <div class="header-brand">
        <img src="images/logo.png" alt="Logo La Gran Ruta" class="logo" />
        <h1 class="title">LA GRAN RUTA</h1>
      </div>

      <nav class="top-nav" aria-label="Menú principal">
        <div class="nav-menu">
          <a href="dashboard.php" class="menu-button <?php echo $current === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
          <a href="inventario.php" class="menu-button <?php echo $current === 'inventario.php' ? 'active' : ''; ?>">Inventario</a>
          <a href="nomina.php" class="menu-button <?php echo $current === 'nomina.php' ? 'active' : ''; ?>">Nómina</a>
          <a href="ventas.php" class="menu-button <?php echo $current === 'ventas.php' ? 'active' : ''; ?>">Ventas</a>
          <a href="logout.php" class="menu-button">Cerrar sesión</a>
        </div>
        <span class="nav-indicator" aria-hidden="true"></span>
      </nav>
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

    (function() {
      function updateIndicator() {
        var nav = document.querySelector('.top-nav');
        if (!nav) return;
        var indicator = nav.querySelector('.nav-indicator');
        var active = nav.querySelector('.menu-button.active');
        if (!indicator) return;
        if (active) {
          var rect = active.getBoundingClientRect();
          var parentRect = nav.getBoundingClientRect();
          var left = rect.left - parentRect.left;
          var width = rect.width;
          indicator.style.left = left + 'px';
          indicator.style.width = width + 'px';
          indicator.classList.remove('hidden');
        } else {
          indicator.classList.add('hidden');
        }
      }

      window.addEventListener('load', updateIndicator);
      window.addEventListener('resize', function() {
        requestAnimationFrame(updateIndicator);
      });

      document.addEventListener('click', function(e) {
        var btn = e.target.closest('.top-nav .menu-button');
        if (!btn) return;
        document.querySelectorAll('.top-nav .menu-button').forEach(function(el) {
          el.classList.remove('active');
        });
        btn.classList.add('active');
        requestAnimationFrame(updateIndicator);
      });
    })();
  </script>
</body>

</html>