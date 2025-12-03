<?php
require_once 'config.php';
session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Manejo AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!empty($_POST['ajax']) && $_POST['ajax'] === '1')) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? 'create';

    try {
        if ($action === 'create' || $action === 'update') {
            $id = intval($_POST['id'] ?? 0);
            $cliente = trim($_POST['cliente'] ?? '');
            $productoNombre = trim($_POST['producto'] ?? '');
            $cantidad = intval($_POST['cantidad'] ?? 0);
            $total = floatval($_POST['total'] ?? 0);
            $fecha = $_POST['fecha'] ?? date('Y-m-d');

            if ($cliente === '' || $productoNombre === '' || $cantidad <= 0) {
                echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
                exit;
            }

            // Iniciar transacción para asegurar integridad del stock
            $pdo->beginTransaction();

            // 1. Obtener ID y stock actual del producto
            $stmtProd = $pdo->prepare("SELECT id, cantidad FROM productos WHERE nombre = ? FOR UPDATE");
            $stmtProd->execute([$productoNombre]);
            $prodData = $stmtProd->fetch(PDO::FETCH_ASSOC);

            if (!$prodData) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
                exit;
            }

            $prodId = $prodData['id'];
            $stockActual = $prodData['cantidad'];

            if ($action === 'create') {
                // Validar stock suficiente
                if ($stockActual < $cantidad) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'error' => "Stock insuficiente. Disponible: $stockActual"]);
                    exit;
                }

                // Insertar venta
                $stmt = $pdo->prepare("INSERT INTO ventas (cliente, producto, cantidad, total, fecha) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$cliente, $productoNombre, $cantidad, $total, $fecha]);

                // Descontar stock
                $stmtUpdate = $pdo->prepare("UPDATE productos SET cantidad = cantidad - ? WHERE id = ?");
                $stmtUpdate->execute([$cantidad, $prodId]);

            } else {
                // UPDATE: Lógica más compleja si se permite editar cantidad y afectar stock.
                // Por simplicidad y seguridad, en este paso solo permitiremos editar datos que NO afecten stock crítico
                // o implementaremos la lógica de reversión y nueva deducción.
                
                // Para este MVP: Si se edita la venta, primero devolvemos el stock original y luego restamos el nuevo.
                
                // 1. Obtener venta original
                $stmtOld = $pdo->prepare("SELECT cantidad, producto FROM ventas WHERE id = ?");
                $stmtOld->execute([$id]);
                $ventaOld = $stmtOld->fetch(PDO::FETCH_ASSOC);

                if (!$ventaOld) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'error' => 'Venta no encontrada']);
                    exit;
                }

                // Si cambió el producto, es más complejo. Asumiremos por ahora que no cambia el producto, o bloqueamos cambio de producto en UI.
                // Si permitimos cambio de producto, habría que devolver stock al producto viejo y restar al nuevo.
                // Aquí simplificamos: Solo validamos stock si el producto es el mismo.
                
                if ($ventaOld['producto'] !== $productoNombre) {
                     // Devolver stock al producto anterior
                     $stmtRestock = $pdo->prepare("UPDATE productos SET cantidad = cantidad + ? WHERE nombre = ?");
                     $stmtRestock->execute([$ventaOld['cantidad'], $ventaOld['producto']]);
                     
                     // Validar stock del nuevo producto (ya lo tenemos en $stockActual)
                     if ($stockActual < $cantidad) {
                        $pdo->rollBack();
                        echo json_encode(['success' => false, 'error' => "Stock insuficiente para el nuevo producto. Disponible: $stockActual"]);
                        exit;
                     }
                     // Restar stock al nuevo
                     $stmtDeduct = $pdo->prepare("UPDATE productos SET cantidad = cantidad - ? WHERE id = ?");
                     $stmtDeduct->execute([$cantidad, $prodId]);

                } else {
                    // El producto es el mismo, ajustar diferencia
                    $diferencia = $cantidad - $ventaOld['cantidad'];
                    // Si diferencia es positiva, necesitamos más stock. Si es negativa, devolvemos stock.
                    
                    if ($diferencia > 0 && $stockActual < $diferencia) {
                        $pdo->rollBack();
                        echo json_encode(['success' => false, 'error' => "Stock insuficiente para aumentar cantidad. Disponible extra: $stockActual"]);
                        exit;
                    }
                    
                    $stmtAdjust = $pdo->prepare("UPDATE productos SET cantidad = cantidad - ? WHERE id = ?");
                    $stmtAdjust->execute([$diferencia, $prodId]);
                }

                $stmt = $pdo->prepare("UPDATE ventas SET cliente = ?, producto = ?, cantidad = ?, total = ?, fecha = ? WHERE id = ?");
                $stmt->execute([$cliente, $productoNombre, $cantidad, $total, $fecha, $id]);
            }

            $pdo->commit();
            echo json_encode(['success' => true]);
            exit;
        }

        if ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID inválido']);
                exit;
            }

            $pdo->beginTransaction();

            // Obtener venta para devolver stock
            $stmtGet = $pdo->prepare("SELECT producto, cantidad FROM ventas WHERE id = ?");
            $stmtGet->execute([$id]);
            $venta = $stmtGet->fetch(PDO::FETCH_ASSOC);

            if ($venta) {
                // Devolver stock
                $stmtRestock = $pdo->prepare("UPDATE productos SET cantidad = cantidad + ? WHERE nombre = ?");
                $stmtRestock->execute([$venta['cantidad'], $venta['producto']]);
            }

            $stmt = $pdo->prepare("DELETE FROM ventas WHERE id = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'error' => 'Error DB: ' . $e->getMessage()]);
        exit;
    }
}

// Cargar ventas
$ventas = [];
try {
    $stmt = $pdo->query("SELECT * FROM ventas ORDER BY fecha DESC, id DESC");
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
}

// Obtener productos con STOCK
$products = [];
try {
    $stmt = $pdo->query("SELECT id, nombre, precio, cantidad FROM productos ORDER BY nombre ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ventas - La Gran Ruta</title>
  <link rel="stylesheet" href="css/styles.css" />
</head>

<body>
  <div class="container">
    <?php include 'includes/header_nav.php'; ?>

    <main class="main-content">
      <h2 class="welcome">Gestión de Ventas</h2>
      <p class="instruction">Registre y consulte las ventas realizadas.</p>
      <!-- Botón abrir formulario -->
      <div style="text-align:center; margin-bottom:18px;">
        <button id="open-form-btn" class="menu-button btn-ventas btn-open-form" type="button">Registrar nueva venta</button>
      </div>

      <!-- Backdrop (sibling) -->
      <div id="modal-backdrop" class="modal-backdrop" aria-hidden="true"></div>

      <!-- Panel deslizante con formulario -->
      <aside id="panel-form" class="form-section form-panel" aria-hidden="true" role="dialog" aria-label="Registrar venta">
        <div class="panel-header-row">
          <h3 id="panel-title">Registrar nueva venta</h3>
          <button id="close-form-btn" class="menu-button btn-close" type="button" aria-label="Cerrar panel">Cerrar ✕</button>
        </div>

        <form id="form-ventas" novalidate>
          <input type="hidden" id="editId" value="">
          <label class="label" for="cliente">Cliente</label>
          <input id="cliente" name="cliente" type="text" placeholder="Nombre del cliente" required />

          <label class="label" for="producto">Producto</label>
          <select id="producto" name="producto" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 12px; font-size: 0.98rem; background: #fff; color: #333;">
            <option value="">Seleccione un producto</option>
            <?php foreach ($products as $p): ?>
                <option value="<?= htmlspecialchars($p['nombre']) ?>" 
                        data-precio="<?= $p['precio'] ?>"
                        data-stock="<?= $p['cantidad'] ?>">
                    <?= htmlspecialchars($p['nombre']) ?> - $<?= number_format($p['precio'], 2) ?> (Stock: <?= $p['cantidad'] ?>)
                </option>
            <?php endforeach; ?>
          </select>
          <small id="stock-info" style="display:block; margin-top:-8px; margin-bottom:10px; color:#666; font-size:0.85rem;"></small>

          <label class="label" for="cantidad">Cantidad</label>
          <input id="cantidad" name="cantidad" type="number" placeholder="Cantidad" min="1" value="1" required />

          <label class="label" for="total">Total</label>
          <input id="total" name="total" type="number" placeholder="Total $" step="0.01" min="0" required readonly />

          <label class="label" for="fecha">Fecha</label>
          <input id="fecha" name="fecha" type="date" required />

          <div style="display:flex; gap:10px; margin-top:12px;">
            <button type="submit" class="menu-button btn-ventas">Guardar</button>
            <button type="button" id="cancel-form-btn" class="menu-button btn-close">Cancelar</button>
          </div>
        </form>
      </aside>

      <!-- Tabla -->
      <table class="inventory-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Total</th>
            <th>Fecha</th>
            <th>Operaciones</th>
          </tr>
        </thead>
        <tbody id="tabla-ventas">
            <?php if (empty($ventas)): ?>
                <tr><td colspan="7" style="text-align:center;">No hay ventas registradas.</td></tr>
            <?php else: ?>
                <?php foreach ($ventas as $v): ?>
                <tr data-vid="<?= $v['id'] ?>">
                    <td data-label="ID"><?= $v['id'] ?></td>
                    <td data-label="Cliente"><?= htmlspecialchars($v['cliente']) ?></td>
                    <td data-label="Producto"><?= htmlspecialchars($v['producto']) ?></td>
                    <td data-label="Cantidad"><?= $v['cantidad'] ?></td>
                    <td data-label="Total">$<?= number_format($v['total'], 2) ?></td>
                    <td data-label="Fecha"><?= htmlspecialchars($v['fecha']) ?></td>
                    <td class="ops-cell" data-label="Operaciones">
                        <button class="op-btn op-edit" 
                            data-id="<?= $v['id'] ?>" 
                            data-cliente="<?= htmlspecialchars($v['cliente']) ?>" 
                            data-producto="<?= htmlspecialchars($v['producto']) ?>" 
                            data-cantidad="<?= $v['cantidad'] ?>" 
                            data-total="<?= $v['total'] ?>" 
                            data-fecha="<?= $v['fecha'] ?>">Editar</button>
                        <button class="op-btn op-delete" data-id="<?= $v['id'] ?>">Eliminar</button>
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

  <!-- Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const openBtn = document.getElementById('open-form-btn');
      const closeBtn = document.getElementById('close-form-btn');
      const cancelBtn = document.getElementById('cancel-form-btn');
      const panel = document.getElementById('panel-form');
      const backdrop = document.getElementById('modal-backdrop');
      const form = document.getElementById('form-ventas');
      const tabla = document.getElementById('tabla-ventas');
      const panelTitle = document.getElementById('panel-title');
      const editIdInput = document.getElementById('editId');
      const clienteInput = document.getElementById('cliente');
      const productoSelect = document.getElementById('producto');
      const cantidadInput = document.getElementById('cantidad');
      const totalInput = document.getElementById('total');
      const fechaInput = document.getElementById('fecha');
      const stockInfo = document.getElementById('stock-info');

      // --- Lógica de cálculo de total y stock ---
      function updateFormState() {
        const option = productoSelect.options[productoSelect.selectedIndex];
        if (!option || !option.value) {
            stockInfo.textContent = '';
            totalInput.value = '';
            return;
        }

        const precio = parseFloat(option.dataset.precio || 0);
        const stock = parseInt(option.dataset.stock || 0);
        const cantidad = parseInt(cantidadInput.value, 10) || 0;
        
        stockInfo.textContent = `Disponible: ${stock} unidades`;
        
        // Validar max stock
        if (cantidad > stock) {
            cantidadInput.setCustomValidity(`Solo hay ${stock} unidades disponibles.`);
            stockInfo.style.color = 'red';
        } else {
            cantidadInput.setCustomValidity('');
            stockInfo.style.color = '#666';
        }

        const total = precio * cantidad;
        totalInput.value = total.toFixed(2);
      }

      productoSelect.addEventListener('change', updateFormState);
      cantidadInput.addEventListener('input', updateFormState);

      function openPanel(mode, data) {
        if (mode === 'create') {
          panelTitle.textContent = 'Registrar nueva venta';
          editIdInput.value = '';
          form.reset();
          fechaInput.valueAsDate = new Date();
          productoSelect.selectedIndex = 0;
          stockInfo.textContent = '';
        } else if (mode === 'edit' && data) {
          panelTitle.textContent = 'Editar venta #' + data.id;
          editIdInput.value = data.id;
          clienteInput.value = data.cliente;
          productoSelect.value = data.producto;
          cantidadInput.value = data.cantidad;
          totalInput.value = data.total;
          fechaInput.value = data.fecha;
          updateFormState(); // Actualizar info de stock al abrir editar
        }
        panel.classList.add('open');
        backdrop.classList.add('open');
        document.body.classList.add('no-scroll');
        requestAnimationFrame(() => clienteInput.focus());
      }

      function closePanel() {
        panel.classList.remove('open');
        backdrop.classList.remove('open');
        document.body.classList.remove('no-scroll');
        form.reset();
        editIdInput.value = '';
      }

      openBtn.addEventListener('click', () => openPanel('create'));
      closeBtn && closeBtn.addEventListener('click', closePanel);
      cancelBtn && cancelBtn.addEventListener('click', closePanel);
      backdrop.addEventListener('click', closePanel);
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closePanel();
      });

      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validación extra frontend
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const id = editIdInput.value;
        const action = id ? 'update' : 'create';
        
        const formData = new FormData(form);
        formData.append('ajax', '1');
        formData.append('action', action);
        if (id) formData.append('id', id);

        try {
            const res = await fetch('ventas.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                location.reload(); 
            } else {
                alert('Error: ' + (data.error || 'Desconocido'));
            }
        } catch (err) {
            console.error(err);
            alert('Error al guardar.');
        }
      });

      // delegación edit/delete
      tabla.addEventListener('click', async function(e) {
        const editBtn = e.target.closest('.op-edit');
        if (editBtn) {
          const data = {
            id: editBtn.dataset.id,
            cliente: editBtn.dataset.cliente,
            producto: editBtn.dataset.producto,
            cantidad: editBtn.dataset.cantidad,
            total: editBtn.dataset.total,
            fecha: editBtn.dataset.fecha
          };
          openPanel('edit', data);
          return;
        }
        const delBtn = e.target.closest('.op-delete');
        if (delBtn) {
          const id = delBtn.dataset.id;
          if (!confirm('¿Confirma eliminar la venta #' + id + '? Se devolverá el stock.')) return;
          
          const formData = new FormData();
          formData.append('ajax', '1');
          formData.append('action', 'delete');
          formData.append('id', id);

          try {
            const res = await fetch('ventas.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Desconocido'));
            }
          } catch (err) {
            console.error(err);
            alert('Error al eliminar.');
          }
        }
      });

      // helpers
      function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, function(m) {
          return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' } [m];
        });
      }
      function escapeHtmlAttr(str) {
        return escapeHtml(String(str)).replace(/"/g, '&quot;');
      }
    });
  </script>
</body>
</html>