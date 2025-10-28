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

---

## Lenguajes y tecnologías (definición rápida)

- PHP
  - Lógica del servidor, autenticación, CRUD y plantillas simples.
  - Uso principal: archivos .php (login.php, registrarse.php, nomina.php, etc.).
- SQL / MySQL
  - Persistencia de datos. Tablas principales: usuarios, empleados.
  - Sentencias DDL/DML en schema.sql o consola MySQL.
- HTML
  - Estructura de las vistas (semántica para accesibilidad).
- CSS
  - Presentación (css/styles.css para UI global, css/login.css para auth).
- JavaScript
  - Interactividad y UX (modal, confirmaciones, indicador del menú).
- Entorno
  - XAMPP en Windows (Apache + MySQL + PHP). Rutas ejemplo: c:\xampp\php\php.exe, c:\xampp\mysql\bin\mysql.exe

---

## Control de versiones — Convenciones para commits y ramas

Objetivo: mensajes claros, reversibles y fáciles de revisar.

1. Modelo de ramas (sugerido)

- main (producción)
- develop (integración)
- feature/<nombre> (nuevas funcionalidades)
- fix/<issue> (correcciones)
- hotfix/<issue> (urgentes sobre main)

2. Formato de mensaje de commit (recomendado - estilo Conventional Commits)

- Estructura:

  - tipo(scope): resumen corto
  - línea en blanco
  - cuerpo opcional (explicar _qué_ y _por qué_, no _cómo_)
  - footer opcional (referencia a issue, breaking changes)

- Tipos comunes:

  - feat: nueva funcionalidad
  - fix: corrección de bug
  - docs: documentación
  - style: formato/estilos sin lógica
  - refactor: reestructuración sin cambio de comportamiento
  - test: pruebas
  - chore: tareas de mantenimiento (scripts, deps)

- Ejemplos:
  - feat(nomina): agregar modal lateral para registrar empleados
  - fix(nomina): corregir eliminación que no recargaba lista
  - docs(readme): añadir guías de commits y lenguajes

3. Comandos básicos (Windows / Git CLI)

- Crear rama de feature:
  ```
  git checkout -b feature/nomina-modal
  ```
- Añadir cambios:
  ```
  git add nomina.php css/styles.css
  ```
  o todo:
  ```
  git add .
  ```
- Hacer commit (ejemplo):
  ```
  git commit -m "feat(nomina): agregar modal lateral para registrar empleados"
  ```
- Subir rama:
  ```
  git push -u origin feature/nomina-modal
  ```
- Crear Pull Request desde la rama -> revisar -> merge a develop/main según flujo.

4. Recomendaciones de commits

- Commits pequeños y atómicos (1 cambio lógico por commit).
- Mensajes claros y en presente imperativo: "Agregar", "Corregir".
- Incluir scope que indique módulo/archivo (nomina, login, styles).
- Usar PRs para revisión; enlazar issue si aplica (ej: #12).

---

## Estructura del proyecto y mapeo a scopes de commits

- / (raíz)
  - README.md — docs (docs)
  - config.php — configuración (chore/config)
- /css
  - styles.css — estilos globales (style)
  - login.css — estilos auth (style)
- /images
  - logo.png, etc. (assets)
- login.php, registrarse.php, logout.php — autenticación (feat/login, fix/login)
- dashboard.php — panel principal, header/top-nav (feat/dashboard)
- nomina.php — módulo Nómina (feat/nomina, fix/nomina)
- inventario.php, mantenimiento.php, ventas.php — otros módulos (feat/inventario, etc.)

Sugerencia: al commitear cambios que afectan varios archivos, usa scope múltiple o describe en el cuerpo del commit:

```
refactor(nomina,styles): unificar clase .form-panel y mejorar accesibilidad

- mover estilos de .form-panel a styles.css
- añadir bloqueo de scroll al abrir modal
```

---

## Workflow sugerido rápido (pasos típicos)

1. Actualiza tu fork/local:
   ```
   git checkout develop
   git pull origin develop
   ```
2. Crear rama de trabajo:
   ```
   git checkout -b feature/<descripcion-corta>
   ```
3. Trabajar y probar localmente (XAMPP).
4. Añadir y commitear con mensaje claro.
5. Push y abrir PR:
   ```
   git push -u origin feature/<descripcion-corta>
   ```
6. Revisar, corregir en la rama si es necesario, merge.

---
