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
  <title>Nómina - La Gran Ruta</title>
  <link rel="stylesheet" href="css/styles.css" />
</head>

<body>
  <div class="container">
    <header class="header">
      <img src="images/logo.png" alt="Logo La Gran Ruta" class="logo" />
      <h1 class="title">LA GRAN RUTA</h1>
    </header>

    <main class="main-content">
      <h2 class="welcome">Gestión de Nómina</h2>
      <p class="instruction">Consulta y administra la nómina del personal.</p>

      <!-- Formulario para nuevo empleado -->
      <section class="form-section">
        <h3>Registrar nuevo empleado</h3>
        <form id="form-nomina">
          <input type="text" placeholder="Nombre completo" required />
          <input type="text" placeholder="Cargo" required />
          <input type="number" placeholder="Salario" required />
          <select required>
            <option value="">Estado</option>
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
          </select>
          <button type="submit" class="menu-button btn-nomina">Registrar</button>
        </form>
      </section>

      <!-- Tabla de empleados -->
      <table class="inventory-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Cargo</th>
            <th>Salario</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody id="tabla-nomina">
          <tr>
            <td>001</td>
            <td>Juan Pérez</td>
            <td>Vendedor</td>
            <td>$1200</td>
            <td>Activo</td>
          </tr>
          <tr>
            <td>002</td>
            <td>Ana Gómez</td>
            <td>Técnico</td>
            <td>$1400</td>
            <td>Activo</td>
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

    <!--
        nomina.php

        Gestión de nómina: interfaz para registrar empleados y consultar la lista localmente.
        El formulario actual construye filas en una tabla en el navegador.

        Para producción:
        - Persistir empleados en la base de datos
        - Añadir validaciones y control de acceso
      -->

    <!-- Script para añadir empleados -->
    <script>
      // Script para añadir empleados a la tabla de nómina
      const form = document.getElementById("form-nomina");
      const tabla = document.getElementById("tabla-nomina");
      let contador = 3;

      form.addEventListener("submit", function(e) {
        e.preventDefault();
        const [nombre, cargo, salario, estado] = Array.from(form.elements).map(input => input.value);

        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td>00${contador++}</td>
            <td>${nombre}</td>
            <td>${cargo}</td>
            <td>$${salario}</td>
            <td>${estado}</td>
          `;
        tabla.appendChild(fila);
        form.reset();
      });
    </script>
  </div>
</body>

</html>