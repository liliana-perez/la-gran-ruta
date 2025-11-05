<?php

/**
 * nomina.php
 *
 * Gestión de nómina (CRUD sobre la tabla `empleados`).
 *
 * Tabla esperada:
 * CREATE TABLE empleados (
 *   id INT AUTO_INCREMENT PRIMARY KEY,
 *   nombre VARCHAR(100) NOT NULL,
 *   cargo VARCHAR(100) NOT NULL,
 *   salario DECIMAL(10,2) NOT NULL,
 *   estado ENUM('Activo','Inactivo') DEFAULT 'Activo'
 * );
 *
 * Funcionalidad:
 * - Maneja acciones POST: create (por defecto), update, delete.
 * - Muestra lista de empleados y ofrece edición/eliminación.
 *
 * Requiere: config.php (variables $host, $user, $pass, $db)
 */

require_once 'config.php';
session_start();

/* Determina el nombre del archivo actual para marcar el item activo en el menú.
   Ejemplo: $current === 'nomina.php' */
$current = basename($_SERVER['PHP_SELF']);

/* Control de acceso básico: redirige a login si no hay sesión válida.
   Reemplazar/expandir con lógica de permisos según se necesite. */
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// Conexión a la base de datos
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die('Error de conexión: ' . $conn->connect_error);
}

$error = '';
$success = '';
// valores de formulario por defecto
$nombre = '';
$cargo = '';
$salario = '';
$estado = 'Activo';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Acción esperada: 'create' (default), 'update' o 'delete'
  $action = $_POST['action'] ?? 'create';

  if ($action === 'delete') {
    // Eliminar empleado
    $delId = intval($_POST['id'] ?? 0);
    if ($delId > 0) {
      $stmtDel = $conn->prepare("DELETE FROM empleados WHERE id = ?");
      $stmtDel->bind_param('i', $delId);
      if ($stmtDel->execute()) {
        // redirigir para evitar reenvío de formulario
        $stmtDel->close();
        $conn->close();
        header('Location: nomina.php');
        exit;
      } else {
        $error = 'Error al eliminar: ' . $stmtDel->error;
        $stmtDel->close();
      }
    } else {
      $error = 'ID inválido para eliminar.';
    }
  } elseif ($action === 'update') {
    // Actualizar empleado existente
    $updateId = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $cargo  = trim($_POST['cargo'] ?? '');
    $salario_raw = trim($_POST['salario'] ?? '');
    $estado = ($_POST['estado'] ?? 'Activo');
    $salario_raw = str_replace(',', '.', $salario_raw);
    $salario = $salario_raw === '' ? null : floatval($salario_raw);

    if ($updateId <= 0 || $nombre === '' || $cargo === '' || $salario === null || $salario <= 0) {
      $error = 'Datos inválidos para actualizar.';
    } elseif (!in_array($estado, ['Activo', 'Inactivo'], true)) {
      $error = 'Estado inválido.';
    } else {
      $stmtUpd = $conn->prepare("UPDATE empleados SET nombre = ?, cargo = ?, salario = ?, estado = ? WHERE id = ?");
      if ($stmtUpd === false) {
        $error = 'Error de consulta: ' . $conn->error;
      } else {
        $stmtUpd->bind_param('ssdsi', $nombre, $cargo, $salario, $estado, $updateId);
        if ($stmtUpd->execute()) {
          $stmtUpd->close();
          $conn->close();
          header('Location: nomina.php');
          exit;
        } else {
          $error = 'Error al actualizar: ' . $stmtUpd->error;
          $stmtUpd->close();
        }
      }
    }
  } else {
    // Crear nuevo empleado (comportamiento existente)
    // Leer y sanitizar entrada
    $nombre = trim($_POST['nombre'] ?? '');
    $cargo  = trim($_POST['cargo'] ?? '');
    $salario_raw = trim($_POST['salario'] ?? '');
    $estado = ($_POST['estado'] ?? 'Activo');

    // Aceptar coma como separador decimal
    $salario_raw = str_replace(',', '.', $salario_raw);
    $salario = $salario_raw === '' ? null : floatval($salario_raw);

    // Validaciones
    if ($nombre === '' || $cargo === '' || $salario === null || $salario <= 0) {
      $error = 'Por favor complete todos los campos correctamente. Salario debe ser mayor a 0.';
    } elseif (!in_array($estado, ['Activo', 'Inactivo'], true)) {
      $error = 'Estado inválido.';
    } else {
      // Insertar usando prepared statement
      $stmt = $conn->prepare("INSERT INTO empleados (nombre, cargo, salario, estado) VALUES (?, ?, ?, ?)");
      if ($stmt === false) {
        $error = 'Error de consulta: ' . $conn->error;
      } else {
        // bind_param: s:string, s:string, d:double, s:string
        $stmt->bind_param('ssds', $nombre, $cargo, $salario, $estado);
        if ($stmt->execute()) {
          $success = 'Empleado registrado correctamente.';
          // limpiar campos
          $nombre = $cargo = $salario = '';
          $estado = 'Activo';
        } else {
          $error = 'Error al guardar: ' . $stmt->error;
        }
        $stmt->close();
      }
    }
  }
}

// Obtener lista de empleados
$empleados = [];
$res = $conn->query("SELECT id, nombre, cargo, salario, estado FROM empleados ORDER BY id ASC");
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $empleados[] = $r;
  }
  $res->free();
}

$conn->close();
?>
<!DOCTYPE html>
<!--
  nomina.php

  Interfaz de nómina (cliente-side): registrar empleados y mostrar la lista.
  - Propósito: gestión básica de empleados para demostración.
  - Mejoras sugeridas: persistencia, validación y control de acceso.
-->
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!--
  Aca es el titulo de la pagina
-->

  <title>Nómina - La Gran Ruta</title>
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
      <h2 class="welcome">Gestión de Nómina</h2>
      <p class="instruction">Consulta y administra la nómina del personal.</p>

      <!-- Mensajes -->
      <?php if ($error !== ''): ?>
        <div class="message message-error" role="alert" aria-live="assertive">
          <span class="message-icon" aria-hidden="true">⚠</span>
          <div class="message-text"><?php echo htmlspecialchars($error); ?></div>
        </div>
      <?php elseif ($success !== ''): ?>
        <div class="message message-success" role="status" aria-live="polite">
          <span class="message-icon" aria-hidden="true">✔</span>
          <div class="message-text"><?php echo htmlspecialchars($success); ?></div>
        </div>
      <?php endif; ?>

      <!-- Formulario para nuevo empleado -->
      <!-- Botón que abre el panel de registro -->
      <div style="text-align:center; margin-bottom:18px;">
        <button id="open-form-btn" class="menu-button btn-nomina btn-open-form" type="button">Registrar nuevo empleado</button>
      </div>

      <!-- Backdrop para el panel (sibling del panel) -->
      <div id="modal-backdrop" class="modal-backdrop" aria-hidden="true"></div>

      <!-- Panel deslizante con formulario (oculto por defecto) -->
      <aside id="panel-form" class="form-section form-panel" aria-hidden="true" role="dialog" aria-label="Registrar empleado">
        <div class="panel-header-row">
          <h3 id="panel-title">Registrar nuevo empleado</h3>
          <button id="close-form-btn" class="menu-button btn-close" type="button" aria-label="Cerrar panel">Cerrar ✕</button>
        </div>
        <form id="form-nomina" method="POST" action="">
          <input type="hidden" name="action" id="form-action" value="create">
          <input type="hidden" name="id" id="empleado-id" value="">
          <label class="label" for="nombre">Nombre</label>
          <input id="nombre" type="text" name="nombre" placeholder="Nombre completo" required value="<?php echo htmlspecialchars($nombre); ?>" />

          <label class="label" for="cargo">Cargo</label>
          <input id="cargo" type="text" name="cargo" placeholder="Cargo" required value="<?php echo htmlspecialchars($cargo); ?>" />

          <label class="label" for="salario">Salario</label>
          <input id="salario" type="text" name="salario" placeholder="Salario (ej. 1200.00)" required value="<?php echo htmlspecialchars($salario); ?>" />

          <label class="label" for="estado">Estado</label>
          <select id="estado" name="estado" required>
            <option value="Activo" <?php echo $estado === 'Activo' ? 'selected' : ''; ?>>Activo</option>
            <option value="Inactivo" <?php echo $estado === 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
          </select>

          <div style="display:flex; gap:10px; margin-top:12px;">
            <button type="submit" class="menu-button btn-nomina">Registrar</button>
            <button type="button" id="cancel-form-btn" class="menu-button btn-close">Cancelar</button>
          </div>
        </form>
      </aside>

      <!-- Tabla de empleados -->
      <table class="inventory-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Cargo</th>
            <th>Salario</th>
            <th>Estado</th>
            <th>Operaciones</th>
          </tr>
        </thead>
        <tbody id="tabla-nomina">
          <?php if (empty($empleados)): ?>
            <tr>
              <td colspan="6" class="muted">No hay empleados registrados.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($empleados as $row): ?>
              <tr>
                <td><?php echo (int)$row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                <td><?php echo htmlspecialchars($row['cargo']); ?></td>
                <td>$<?php echo number_format((float)$row['salario'], 2, '.', ','); ?></td>
                <td><?php echo htmlspecialchars($row['estado']); ?></td>
                <td class="ops-cell">
                  <!-- Edit: abre el panel en modo edición -->
                  <button
                    type="button"
                    class="op-btn op-edit"
                    data-id="<?php echo (int)$row['id']; ?>"
                    data-nombre="<?php echo htmlspecialchars($row['nombre'], ENT_QUOTES); ?>"
                    data-cargo="<?php echo htmlspecialchars($row['cargo'], ENT_QUOTES); ?>"
                    data-salario="<?php echo htmlspecialchars($row['salario'], ENT_QUOTES); ?>"
                    data-estado="<?php echo htmlspecialchars($row['estado'], ENT_QUOTES); ?>"
                    title="Editar">Editar</button>

                  <!-- Delete: formulario POST con confirmación -->
                  <form method="POST" action="" class="inline-form delete-form" onsubmit="return false;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                    <button type="submit" class="op-btn op-delete" title="Eliminar">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
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

  <!-- Ya no se necesita el script que añadía filas en el cliente -->
  <script src="js/nomina.js"></script>
</body>

</html>