<?php

/**
 * inventario.php
 * - Interfaz de gestión de inventario + persistencia en tabla `productos`.
 * - Procesa peticiones AJAX (POST, campo ajax=1) para create/update/delete.
 */

require_once 'config.php';
session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Manejo AJAX (crear/actualizar/eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!empty($_POST['ajax']) && $_POST['ajax'] === '1')) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? 'create';

    try {
        if ($action === 'create') {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $cantidad = intval($_POST['cantidad'] ?? 0);
            $precio = floatval(str_replace(',', '.', $_POST['precio'] ?? 0));

            if ($codigo === '' || $nombre === '' || $cantidad < 0) {
                echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO productos (codigo, nombre, cantidad, precio) VALUES (?, ?, ?, ?)");
            $stmt->execute([$codigo, $nombre, $cantidad, $precio]);
            $id = $pdo->lastInsertId();

            echo json_encode(['success' => true, 'id' => $id, 'data' => ['codigo' => $codigo, 'nombre' => $nombre, 'cantidad' => $cantidad, 'precio' => number_format($precio, 2, '.', '')]]);
            exit;
        }

        if ($action === 'update') {
            $id = intval($_POST['id'] ?? 0);
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $cantidad = intval($_POST['cantidad'] ?? 0);
            $precio = floatval(str_replace(',', '.', $_POST['precio'] ?? 0));

            if ($id <= 0 || $codigo === '' || $nombre === '') {
                echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE productos SET codigo = ?, nombre = ?, cantidad = ?, precio = ? WHERE id = ?");
            $stmt->execute([$codigo, $nombre, $cantidad, $precio, $id]);
            echo json_encode(['success' => true]);
            exit;
        }

        if ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID inválido']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            exit;
        }

        echo json_encode(['success' => false, 'error' => 'Acción no reconocida']);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
        exit;
    }
}

// --- Nuevo: endpoint GET para cargar productos iniciales desde la BD ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['load'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $stmt = $pdo->query("SELECT id, codigo, nombre, cantidad, precio FROM productos ORDER BY id ASC");
        $out = [];
        while ($r = $stmt->fetch()) {
            $r['precio'] = number_format((float)$r['precio'], 2, '.', '');
            $out[] = $r;
        }
        echo json_encode($out);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Si no es AJAX, continúa y muestra la página (HTML + JS que usa AJAX)
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inventario - La Gran Ruta</title>
  <link rel="stylesheet" href="css/styles.css" />
</head>

<body>
  <div class="container">
    <?php include 'includes/header_nav.php'; ?>

    <main class="main-content">
      <h2 class="welcome">Gestión de Inventario</h2>
      <p class="instruction">Agregue, edite o elimine productos del inventario.</p>
      <div style="text-align:center; margin-bottom:18px;">
        <button id="open-form-btn" class="menu-button btn-inventario btn-open-form" type="button">Registrar nuevo producto</button>
      </div>

      <!-- Backdrop (sibling) -->
      <div id="modal-backdrop" class="modal-backdrop" aria-hidden="true"></div>

      <!-- Panel deslizante con formulario (oculto por defecto) -->
      <aside id="panel-form" class="form-section form-panel" aria-hidden="true" role="dialog" aria-label="Registrar producto">
        <div class="panel-header-row">
          <h3 id="panel-title">Registrar nuevo producto</h3>
          <button id="close-form-btn" class="menu-button btn-close" type="button" aria-label="Cerrar panel">Cerrar ✕</button>
        </div>
        <form id="form-inventario" novalidate>
          <input type="hidden" id="editId" value="">
          <label class="label" for="codigo">Código</label>
          <input id="codigo" name="codigo" type="text" placeholder="Código del producto" required />

          <label class="label" for="nombre">Nombre</label>
          <input id="nombre" name="nombre" type="text" placeholder="Nombre del producto" required />

          <label class="label" for="cantidad">Cantidad</label>
          <input id="cantidad" name="cantidad" type="number" placeholder="Cantidad" min="0" required />

          <label class="label" for="precio">Precio</label>
          <input id="precio" name="precio" type="number" placeholder="Precio unitario ($)" step="0.01" min="0" required />

          <div style="display:flex; gap:10px; margin-top:12px;">
            <button type="submit" class="menu-button btn-inventario">Guardar</button>
            <button type="button" id="cancel-form-btn" class="menu-button btn-close">Cancelar</button>
          </div>
        </form>
      </aside>

      <!-- Tabla -->
      <table class="inventory-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Operaciones</th>
          </tr>
        </thead>
        <tbody id="tabla-inventario">
          <!-- Productos se agregarán dinámicamente aquí -->
        </tbody>
      </table>

      <div class="return-menu">
        <a href="dashboard.php" class="menu-button btn-inicio">Volver al inicio</a>
      </div>
    </main>

    <footer class="footer">
      <p class="rights">Todos los derechos reservados</p>
    </footer>

  </div>
  <script src="js/inventario.js"></script>
</body>

</html>