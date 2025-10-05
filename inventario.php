<!DOCTYPE html>
<!--
  inventario.php

  Interfaz de gestión de inventario (cliente-side).
  - Propósito: permitir añadir, editar y eliminar productos en una tabla HTML.
  - Nota: Actualmente los cambios solo existen en memoria del navegador. Para
    persistencia, crear endpoints PHP que acepten/retornen JSON y usar fetch/AJAX.
-->
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inventario - La Gran Ruta</title>
  <link rel="stylesheet" href="css/styles.css" />
</head>

<body>
  <div class="container">
    <header class="header">
      <img src="images/logo.png" alt="Logo La Gran Ruta" class="logo" />
      <h1 class="title">LA GRAN RUTA</h1>
    </header>

    <main class="main-content">
      <h2 class="welcome">Gestión de Inventario</h2>
      <p class="instruction">Agregue, edite o elimine productos del inventario.</p>

      <!-- Formulario -->
      <section class="form-section">
        <h3 id="form-title">Agregar nuevo producto</h3>
        <form id="form-inventario">
          <input type="text" id="codigo" placeholder="Código del producto" required />
          <input type="text" id="nombre" placeholder="Nombre del producto" required />
          <input type="number" id="cantidad" placeholder="Cantidad" min="1" required />
          <input type="number" id="precio" placeholder="Precio unitario ($)" min="0" required />
          <button type="submit" class="menu-button btn-inventario">Guardar</button>
          <input type="hidden" id="editIndex" />
        </form>
      </section>

      <!-- Tabla -->
      <table class="inventory-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tabla-inventario">
          <!-- Productos se agregarán dinámicamente aquí -->
        </tbody>
      </table>

      <div class="return-menu">
        <a href="index.html" class="menu-button btn-inicio">Volver al Menú</a>
      </div>
    </main>

    <footer class="footer">
      <p class="rights">Todos los derechos reservados</p>
    </footer>

    <!--
      inventario.php

      Interfaz de gestión de inventario (cliente-side): permite añadir, editar y eliminar
      filas en una tabla HTML mediante JavaScript. Los datos actualmente solo se mantienen
      en memoria del navegador. Para persistencia, integrar con un endpoint PHP que guarde
      en la base de datos.

      Flujo del script:
      - `form-inventario` captura el submit y añade una fila en la tabla
      - `editarFila(boton)` y `eliminarFila(boton)` gestionan edición y borrado
    -->

    <script>
      // Código JS para manipular la tabla de inventario
      // Variables DOM principales
      const form = document.getElementById("form-inventario");
      const tabla = document.getElementById("tabla-inventario");
      const formTitle = document.getElementById("form-title");
      const codigoInput = document.getElementById("codigo");
      const nombreInput = document.getElementById("nombre");
      const cantidadInput = document.getElementById("cantidad");
      const precioInput = document.getElementById("precio");
      const editIndexInput = document.getElementById("editIndex");

      let contador = 1;

      // Maneja el envío del formulario: crea o edita filas en la tabla
      form.addEventListener("submit", function(e) {
        e.preventDefault();
        const codigo = codigoInput.value;
        const nombre = nombreInput.value;
        const cantidad = cantidadInput.value;
        const precio = precioInput.value;
        const editIndex = editIndexInput.value;

        if (editIndex === "") {
          const fila = document.createElement("tr");
          fila.innerHTML = `
            <td>00${contador++}</td>
            <td>${codigo}</td>
            <td>${nombre}</td>
            <td>${cantidad}</td>
            <td>$${precio}</td>
            <td>
              <button onclick="editarFila(this)">Editar</button>
              <button onclick="eliminarFila(this)">Eliminar</button>
            </td>
          `;
          tabla.appendChild(fila);
        } else {
          const fila = tabla.rows[editIndex];
          fila.cells[1].textContent = codigo;
          fila.cells[2].textContent = nombre;
          fila.cells[3].textContent = cantidad;
          fila.cells[4].textContent = `$${precio}`;
          editIndexInput.value = "";
          formTitle.textContent = "Agregar nuevo producto";
        }

        form.reset();
      });

      // Funciones auxiliares: editarFila, eliminarFila
      function editarFila(boton) {
        const fila = boton.parentElement.parentElement;
        const index = fila.rowIndex - 1;
        const celdas = fila.cells;

        codigoInput.value = celdas[1].textContent;
        nombreInput.value = celdas[2].textContent;
        cantidadInput.value = celdas[3].textContent;
        precioInput.value = celdas[4].textContent.replace('$', '');
        editIndexInput.value = index;
        formTitle.textContent = "Editar producto";
      }

      function eliminarFila(boton) {
        if (confirm("¿Deseas eliminar este producto del inventario?")) {
          const fila = boton.parentElement.parentElement;
          fila.remove();
        }
      }
    </script>
  </div>
</body>

</html>