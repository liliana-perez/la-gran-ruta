<!-- ...existing code... -->

# La Gran Ruta — Documentación del proyecto

Resumen

- Aplicación web PHP sencilla para gestión: autenticación (registro/login), dashboard, inventario, nómina, mantenimiento y ventas.
- Código orientado a XAMPP (Windows). Usa MySQL y PHP sin frameworks.

Requisitos

- Windows + XAMPP (Apache, MySQL, PHP). PHP >= 7.2 recomendado.
- Editor: VS Code (opcional).
- Navegador moderno (Chrome, Edge, Firefox).

Estructura principal de archivos

- config.php — configuración de conexión (host, user, pass, db). Reemplazar credenciales en desarrollo/producción.
- login.php — formulario y lógica de inicio de sesión (password_verify, sessions).
- registrarse.php (o registrarse.php) — formulario de registro (password_hash, prepared statements).
- logout.php — confirmación y cierre de sesión (token CSRF en sesión).
- dashboard.php — vista principal con menú superior (resalta página activa).
- nomina.php — CRUD de empleados (tabla `empleados`, formulario modal, editar/eliminar).
- inventario.php, mantenimiento.php, ventas.php — vistas modulares.
- css/styles.css — estilos globales (header, top-nav, tablas, modal).
- css/login.css — estilos específicos para login/registro.
- images/ — logos e imágenes.
- README.md — esta documentación.

Instalación rápida (XAMPP)

1. Copia o clona el repositorio en:
   c:\xampp\htdocs\la_gran_ruta
2. Inicia Apache y MySQL desde XAMPP Control Panel.
3. Configura la base de datos en config.php (o usa variables de entorno).
4. Crear la base de datos y tablas (ejemplo):

   - Crear base de datos (MySQL):

   ```
   CREATE DATABASE la_gran_ruta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE la_gran_ruta;
   ```

   - Crear tabla usuarios:

   ```
   CREATE TABLE usuarios (
     id_usuario INT AUTO_INCREMENT PRIMARY KEY,
     nombre VARCHAR(150) NOT NULL,
     correo VARCHAR(200) NOT NULL UNIQUE,
     clave VARCHAR(255) NOT NULL,
     creado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

   - Crear tabla empleados (nómina):

   ```
   CREATE TABLE empleados (
     id INT AUTO_INCREMENT PRIMARY KEY,
     nombre VARCHAR(100) NOT NULL,
     cargo VARCHAR(100) NOT NULL,
     salario DECIMAL(10,2) NOT NULL,
     estado ENUM('Activo','Inactivo') DEFAULT 'Activo'
   );
   ```

   - Comando rápido (Windows, usando XAMPP mysql):

   ```
   c:\xampp\mysql\bin\mysql -u root -p < schema.sql
   ```

Uso

- Abrir en el navegador:
  http://localhost/la_gran_ruta/login.php
- Crear cuenta: registrarse.php → al registrarse redirige a login.php?registered=1
- Iniciar sesión → dashboard.php
- Desde dashboard se navega a Nómina (nomina.php) para gestionar empleados.

Flujos importantes y seguridad

- Registro: contraseña hasheada con `password_hash()` antes de insertar.
- Login: `password_verify()` + compatibilidad con texto plano heredado (si existe).
- Todas las consultas usan prepared statements para evitar SQL Injection.
- Logout usa token CSRF simple guardado en sesión.
- Recomendaciones:
  - Mover credenciales fuera de config.php y usar variables de entorno (getenv).
  - Forzar HTTPS en producción.
  - Añadir límite de intentos de login y registro de IP para evitar ataques por fuerza bruta.
  - Añadir CSRF tokens a todos los formularios que modifican datos.
  - Validar y sanear datos en servidor siempre (no confiar en validación cliente).

Detalles técnicos y comandos útiles

- Lint PHP (usar PHP de XAMPP):
  ```
  c:\xampp\php\php.exe -l "c:\xampp\htdocs\la_gran_ruta\login.php"
  ```
- Reiniciar Apache/MySQL: Abrir XAMPP Control Panel.
- Verificar carga de CSS/JS: DevTools → Network (ver 200 / 404).
- Limpiar caché del navegador: Ctrl+F5.

Estilos y UI

- css/styles.css: layout del header, menú superior (top-nav) con indicador deslizante y estilos del modal.
- css/login.css: formulario de login, registro y mensajes (error/success).
- Nomina: formulario en panel modal lateral (abrir con botón _Registrar nuevo empleado_), tabla con columna Operaciones (Editar/Eliminar).

Funcionalidad añadida

- Panel modal (abrir/cerrar con backdrop, bloqueo de scroll, accesibilidad: focus y Esc).
- CRUD en nomina.php:
  - Crear: form -> POST action=create
  - Editar: abre modal rellenado -> action=update
  - Eliminar: POST action=delete (confirmación JS)
- Menú superior: marcado del item activo vía PHP ($current = basename($\_SERVER['PHP_SELF'])) y posicionamiento del indicador con JS.

Buenas prácticas recomendadas

- Extraer header/footer a includes (inc/header.php, inc/footer.php) para evitar repetición.
- Implementar manejo de errores más robusto (logs en archivo fuera del webroot).
- Usar Composer y PSR-4 si el proyecto escala.
- Añadir pruebas unitarias / integración si la lógica de negocio crece.

Problemas comunes y solución

- CSS no se carga:
  - Verificar ruta: css/styles.css y permisos.
  - Quitar cache: Ctrl+F5.
  - Revisar Network en DevTools.
- Modal no aparece o queda detrás:
  - Asegurar que `.modal-backdrop` y `.form-panel` estén hermanos y z-index altos.
  - Revisar consola JS por errores.
- Sesiones:
  - Si login no persiste, revisar session.save_path y permisos.
  - Asegurar session_start() al inicio de cada archivo que use sesión.

Migración y mejora

- Reemplazar config.php por lectura desde .env (ej. vlucas/phpdotenv) o variables de entorno del servidor.
- Implementar roles/permiso por usuario (admin, usuario estándar).
- Poner rate-limiter para endpoints de autenticación.

Contacto / próximos pasos

- Si dices “quiero que cree inc/header.php e incluya en todas las páginas”, lo agrego.
- Puedo generar un script SQL completo (schema.sql) y/o un sencillo script de migración en PHP.

Licencia

- Código sin licencia explícita: añadir LICENSE según tu preferencia (MIT recomendado para proyectos de ejemplo).

Fin

- Documentación generada para facilitar despliegue, mantenimiento y extensibilidad. Si quieres que cree los archivos de include, variables de entorno, o un script `install.php` que genere las tablas, lo implemento.
