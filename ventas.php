<!DOCTYPE html>
<!--
  ventas.php

  Registro de ventas (cliente-side): permite agregar, editar y eliminar ventas
  en una tabla HTML. Actualmente no persiste los datos en servidor.
  - Recomendación: crear endpoints PHP para guardado y consulta de ventas.
-->
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ventas - La Gran Ruta</title>
  <link rel="stylesheet" href="css/styles.css" />
</head>

<body>
  <div class="container">
    <header class="header">
      <img src="images/logo.png" alt="Logo La Gran Ruta" class="logo" />
      <h1 class="title">LA GRAN RUTA</h1>
    </header>

    <main class="main-content">
      <h2 class="welcome">Gestión de Ventas</h2>
      <p class="instruction">Registre y consulte las ventas realizadas.</p>

      <!-- Formulario -->
      <section class="form-section">
        <h3 id="form-title">Registrar nueva venta</h3>
        <form id="form-ventas">
          <input type="text" id="cliente" placeholder="Nombre del Cliente" required />
          <input type="text" id="producto" placeholder="Producto vendido" required />
          <input type="number" id="cantidad" placeholder="Cantidad" min="1" required />
          <input type="number" id="total" placeholder="Total $" min="0" required />
          <input type="date" id="fecha" required />
          <button type="submit" class="menu-button btn-ventas">Guardar</button>
          <input type="hidden" id="editIndex" />
        </form>
      </section>

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
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tabla-ventas">
          <tr>
            <td>001</td>
            <td>Carlos López</td>
            <td>Bicicleta MTB</td>
            <td>1</td>
            <td>$1500</td>
            <td>2025-06-06</td>
            <td>
              <button onclick="editarFila(this)">Editar</button>
              <button onclick="eliminarFila(this)">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="return-menu">
        <a href="index.html" class="menu-button btn-inicio">Volver al Menú</a>
      </div>
    </main>

    <footer class="footer">
      <p class="rights">Todos los derechos reservados</p>
    </footer>

    <!-- Script -->
    <script>
      // Script para gestionar ventas: añadir, editar y eliminar filas de la tabla
      const form = document.getElementById("form-ventas");
      const tabla = document.getElementById("tabla-ventas");
      const formTitle = document.getElementById("form-title");
      const clienteInput = document.getElementById("cliente");
      const productoInput = document.getElementById("producto");
      const cantidadInput = document.getElementById("cantidad");
      const totalInput = document.getElementById("total");
      const fechaInput = document.getElementById("fecha");
      const editIndexInput = document.getElementById("editIndex");
      let contador = 2;

      form.addEventListener("submit", function(e) {
        e.preventDefault();

        const cliente = clienteInput.value;
        const producto = productoInput.value;
        const cantidad = cantidadInput.value;
        const total = totalInput.value;
        const fecha = fechaInput.value;
        const editIndex = editIndexInput.value;

        if (editIndex === "") {
          const fila = document.createElement("tr");
          fila.innerHTML = `
              <td>00${contador++}</td>
              <td>${cliente}</td>
              <td>${producto}</td>
              <td>${cantidad}</td>
              <td>$${total}</td>
              <td>${fecha}</td>
              <td>
                <button onclick="editarFila(this)">Editar</button>
                <button onclick="eliminarFila(this)">Eliminar</button>
              </td>
            `;
          tabla.appendChild(fila);
        } else {
          const fila = tabla.rows[editIndex];
          fila.cells[1].textContent = cliente;
          fila.cells[2].textContent = producto;
          fila.cells[3].textContent = cantidad;
          fila.cells[4].textContent = `$${total}`;
          fila.cells[5].textContent = fecha;
          editIndexInput.value = "";
          formTitle.textContent = "Registrar nueva venta";
        }

        form.reset();
      });

      function editarFila(boton) {
        const fila = boton.parentElement.parentElement;
        const index = fila.rowIndex - 1;
        const c = fila.cells;

        clienteInput.value = c[1].textContent;
        productoInput.value = c[2].textContent;
        cantidadInput.value = c[3].textContent;
        totalInput.value = c[4].textContent.replace('$', '');
        fechaInput.value = c[5].textContent;
        editIndexInput.value = index;
        formTitle.textContent = "Editar venta";
      }

      function eliminarFila(boton) {
        if (confirm("¿Deseas eliminar esta venta?")) {
          const fila = boton.parentElement.parentElement;
          fila.remove();
        }
      }
    </script>
  </div>
</body>

</html>

<!--
  ventas.php

  Página para registrar ventas. El comportamiento actual es totalmente cliente-side
  (no hay persistencia). Se generan filas nuevas en la tabla con los datos del formulario.

  Mejoras sugeridas:
  - Agregar endpoints PHP para almacenar ventas en la base de datos
  - Control de acceso y validación de entradas
-->