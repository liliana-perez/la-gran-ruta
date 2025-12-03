<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = basename($_SERVER['PHP_SELF']);
?>
<header class="header">
  <div class="header-brand">
    <img src="images/logo.png" alt="Logo La Gran Ruta" class="logo" />
    <h1 class="title">LA GRAN RUTA</h1>
  </div>

  <nav class="top-nav" aria-label="Menú principal">
    <div class="nav-menu">
      <a href="dashboard.php" class="menu-button <?php echo $current === 'dashboard.php' ? 'active' : ''; ?>"><span class="icon">🏠</span>Dashboard</a>
      <a href="inventario.php" class="menu-button <?php echo $current === 'inventario.php' ? 'active' : ''; ?>"><span class="icon">📦</span>Inventario</a>
      <a href="nomina.php" class="menu-button <?php echo $current === 'nomina.php' ? 'active' : ''; ?>"><span class="icon">👥</span>Nómina</a>
      <a href="ventas.php" class="menu-button <?php echo $current === 'ventas.php' ? 'active' : ''; ?>"><span class="icon">💰</span>Ventas</a>
      <a href="logout.php" class="menu-button"><span class="icon">🔓</span>Cerrar sesión</a>
    </div>
    <span class="nav-indicator" aria-hidden="true"></span>
  </nav>
</header>
<script>
  // Script para la animación del indicador del menú
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

    // Ejecutar también inmediatamente por si acaso
    if (document.readyState === 'complete') {
        updateIndicator();
    }
  })();
</script>
