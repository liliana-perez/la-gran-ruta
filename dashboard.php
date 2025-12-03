<?php

/**
 * dashboard.php
 *
 * Página principal que se muestra tras la autenticación.
 * Requisitos:
 * - Debe existir una sesión válida con $_SESSION['user_id'] y $_SESSION['user_name'].
 *
 * Funcionalidad:
 * - Muestra un saludo personalizado.
 * - Resumen de Ventas (Total Ventas, Ingresos).
 * - Gráfico de Stock de Productos.
 */

require_once 'config.php';
session_start();

// Si no hay sesión válida, redirigir al login
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$userName = $_SESSION['user_name'] ?? 'Usuario';
$current = basename($_SERVER['PHP_SELF']);

// --- Lógica de Dashboard ---

// 1. Resumen de Ventas
$totalVentas = 0;
$ingresosTotales = 0;
$ventasRecientes = 0; // Ventas de hoy

try {
    // Total Ventas e Ingresos
    $stmt = $pdo->query("SELECT COUNT(*) as count, SUM(total) as revenue FROM ventas");
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalVentas = $res['count'] ?? 0;
    $ingresosTotales = $res['revenue'] ?? 0;

    // Ventas de hoy
    $hoy = date('Y-m-d');
    $stmtHoy = $pdo->prepare("SELECT COUNT(*) as count FROM ventas WHERE fecha = ?");
    $stmtHoy->execute([$hoy]);
    $ventasRecientes = $stmtHoy->fetchColumn();

} catch (PDOException $e) {
    // Manejo silencioso o log de error
}

// 2. Datos para Gráfico de Stock
$productosLabels = [];
$productosStock = [];
$lowStockCount = 0;

try {
    $stmtProd = $pdo->query("SELECT nombre, cantidad FROM productos ORDER BY cantidad ASC");
    while ($row = $stmtProd->fetch(PDO::FETCH_ASSOC)) {
        $productosLabels[] = $row['nombre'];
        $productosStock[] = $row['cantidad'];
        if ($row['cantidad'] < 5) {
            $lowStockCount++;
        }
    }
} catch (PDOException $e) {
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - La Gran Ruta</title>
  <link rel="stylesheet" href="css/styles.css" />
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
  <div class="container">
    <?php include 'includes/header_nav.php'; ?>

    <main class="main-content">
      <h2 class="welcome">BIENVENIDO, <?php echo htmlspecialchars($userName); ?></h2>
      <p class="instruction">Resumen general del negocio</p>

      <!-- Tarjetas de Resumen -->
      <div class="dashboard-cards">
        <div class="card card-blue">
            <h3>Total Ventas</h3>
            <p class="card-number"><?php echo $totalVentas; ?></p>
            <small>Ventas registradas</small>
        </div>
        <div class="card card-green">
            <h3>Ingresos Totales</h3>
            <p class="card-number">$<?php echo number_format($ingresosTotales, 2); ?></p>
            <small>Acumulado histórico</small>
        </div>
        <div class="card card-orange">
            <h3>Ventas Hoy</h3>
            <p class="card-number"><?php echo $ventasRecientes; ?></p>
            <small><?php echo date('d/m/Y'); ?></small>
        </div>
        <div class="card <?php echo $lowStockCount > 0 ? 'card-red' : 'card-gray'; ?>">
            <h3>Alerta Stock</h3>
            <p class="card-number"><?php echo $lowStockCount; ?></p>
            <small>Productos con < 5 unidades</small>
        </div>
      </div>

      <!-- Sección de Gráfico -->
      <div class="chart-section">
        <h3>Niveles de Inventario</h3>
        <div class="chart-container">
            <canvas id="stockChart"></canvas>
        </div>
      </div>

      <div class="spacer-small"></div>
    </main>

    <footer class="footer">
      <p class="rights">Todos los derechos reservados</p>
    </footer>
  </div>

  <script>
    // Configuración del Gráfico
    const ctx = document.getElementById('stockChart').getContext('2d');
    const stockChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($productosLabels); ?>,
            datasets: [{
                label: 'Stock Disponible',
                data: <?php echo json_encode($productosStock); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Cantidad'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
  </script>
</body>

</html>