<!DOCTYPE html>
<!--
  mantenimiento.php

  Interfaz de gestión de tareas de mantenimiento (cliente-side).
  - Propósito: registrar y editar tareas; actualmente la persistencia es local.
  - Recomendación: agregar endpoints backend para almacenar registros en BD.
-->
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mantenimiento - La Gran Ruta</title>
  <link rel="stylesheet" href="css/styles.css" />
</head>

<body>
  <div class="container">
    <header class="header">
      <img src="images/logo.png" alt="Logo La Gran Ruta" class="logo" />
      <h1 class="title">LA GRAN RUTA</h1>
    </header>

    <main class="main-content">
      <h2 class="welcome">Gestión de Mantenimiento</h2>
      <p class="instruction">Registre y consulte las tareas de mantenimiento realizadas.</p>

      <!-- Formulario -->
      <section class="form-section">
        <h3 id="form-title">Registrar nuevo mantenimiento</h3>
        <form id="form-mantenimiento">
          <input type="text" id="idBici" placeholder="ID Bicicleta" required />
          <input type="text" id="tipo" placeholder="Tipo de Mantenimiento" required />
          <input type="date" id="fecha" required />
          <textarea id="obs" placeholder="Observaciones" rows="3"></textarea>
          <button type="submit" class="menu-button btn-mantenimiento">Guardar</button>
          <input type="hidden" id="editIndex" />
        </form>
      </section>

      <!-- Tabla -->
      <table class="inventory-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>ID Bicicleta</th>
            <th>Tipo</th>
            <th>Fecha</th>
            <th>Observaciones</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tabla-mantenimiento">
          <tr>
            <td>001</td>
            <td>MTB001</td>
            <td>Revisión General</td>
            <td>2025-06-01</td>
            <td>Cadena ajustada, frenos revisados</td>
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
    <!--
        mantenimiento.php

        Página para gestionar registros de mantenimiento. La UI actual trabaja en el lado
        del cliente (JavaScript) y no persiste los cambios en la base de datos.

        Recomendaciones:
        - Añadir endpoints PHP (por ejemplo, mantenimientos_save.php) que acepten POST/GET
          para persistir los registros y devolver JSON.
        - Validar entradas en el servidor antes de guardar.
      -->
    <script>
      // Script para gestionar la tabla de mantenimientos
      // - Captura el envío del formulario y añade/edita filas
      // - Funciones: editarFila, eliminarFila
      const form = document.getElementById("form-mantenimiento");
      const tabla = document.getElementById("tabla-mantenimiento");
      const formTitle = document.getElementById("form-title");
      const idBiciInput = document.getElementById("idBici");
      const tipoInput = document.getElementById("tipo");
      const fechaInput = document.getElementById("fecha");
      const obsInput = document.getElementById("obs");
      const editIndexInput = document.getElementById("editIndex");
      let contador = 2;

      form.addEventListener("submit", function(e) {
        e.preventDefault();

        const idBici = idBiciInput.value;
        const tipo = tipoInput.value;
        const fecha = fechaInput.value;
        const obs = obsInput.value;
        const editIndex = editIndexInput.value;

        if (editIndex === "") {
          const fila = document.createElement("tr");
          fila.innerHTML = `
              <td>00${contador++}</td>
              <td>${idBici}</td>
              <td>${tipo}</td>
              <td>${fecha}</td>
              <td>${obs}</td>
              <td>
                <button onclick="editarFila(this)">Editar</button>
                <button onclick="eliminarFila(this)">Eliminar</button>
              </td>
            `;
          tabla.appendChild(fila);
        } else {
          const fila = tabla.rows[editIndex];
          fila.cells[1].textContent = idBici;
          fila.cells[2].textContent = tipo;
          fila.cells[3].textContent = fecha;
          fila.cells[4].textContent = obs;
          editIndexInput.value = "";
          formTitle.textContent = "Registrar nuevo mantenimiento";
        }

        form.reset();
      });

      function editarFila(boton) {
        const fila = boton.parentElement.parentElement;
        const index = fila.rowIndex - 1; // restamos el encabezado
        const celdas = fila.cells;

        idBiciInput.value = celdas[1].textContent;
        tipoInput.value = celdas[2].textContent;
        fechaInput.value = celdas[3].textContent;
        obsInput.value = celdas[4].textContent;
        editIndexInput.value = index;
        formTitle.textContent = "Editar mantenimiento";
      }

      function eliminarFila(boton) {
        const fila = boton.parentElement.parentElement;
        fila.remove();
      }
    </script>
  </div>
</body>

</html>