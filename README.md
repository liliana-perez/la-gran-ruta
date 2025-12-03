# La Gran Ruta

## Descripción del proyecto

Aplicación web de gestión para **La Gran Ruta**, una pequeña empresa que necesita controlar su inventario, ventas y nómina. El proyecto está construido con **PHP**, **PDO** para acceso a base de datos, **HTML**, **CSS** y **JavaScript** (vanilla). Se ha implementado un diseño moderno y responsivo con iconos, tarjetas y animaciones para ofrecer una experiencia premium.

## Funcionalidades principales (cambios realizados)

- **Inventario (`inventario.php`)**
  - Refactorizado a **PDO** y centralizado en `config.php`.
  - Validación de sesión en todas las páginas.
  - CRUD de productos mediante **AJAX** (`js/inventario.js`).
  - Diseño responsivo con tarjetas y estilos modernos en `css/styles.css`.

- **Ventas (`ventas.php`)**
  - Selección de productos cargada dinámicamente desde la tabla `productos`.
  - Cálculo automático del total de la venta.
  - Persistencia completa (CRUD) de ventas en la nueva tabla `ventas` usando PDO.
  - Gestión de stock con transacciones: deducción al crear/actualizar y restauración al eliminar.
  - UI que se actualiza sin recargar la página (similar a inventario).

- **Nómina (`nomina.php`)**
  - Migrado a **PDO** para mantener consistencia.

- **Navegación común**
  - Menú extraído a `includes/header_nav.php` y reutilizado en todas las páginas internas.
  - Cada ítem del menú incluye un **icono emoji** (🏠, 📦, 👥, 💰, 🔓).
  - Indicador animado que resalta la página activa.

- **Dashboard (`dashboard.php`)**
  - Resumen de métricas de negocio: total de ventas, ingresos totales, ventas de hoy y alerta de stock bajo.
  - Gráfica de niveles de inventario usando **Chart.js**.
  - Tarjetas de resumen con colores y micro‑animaciones.

- **Estilos (`css/styles.css`)**
  - Sistema de diseño premium: colores armoniosos, tipografía, sombras, transiciones.
  - Nuevas clases para tarjetas del dashboard y menú.

- **Base de datos (`schema.sql`)**
  - Tablas: `productos`, `ventas`, `empleados`.
  - Scripts de creación y ejemplos de datos.

## Requisitos

- **Servidor web** con PHP 7.4+ (compatible con XAMPP).
- **MySQL / MariaDB**.
- Extensión PDO habilitada.
- Conexión configurada en `config.php` (host, dbname, user, password).

## Instalación

1. Clonar o copiar el proyecto en el directorio de tu servidor (ejemplo: `c:\xampp\htdocs\la_gran_ruta`).
2. Importar `schema.sql` en tu base de datos:
   ```bash
   mysql -u tu_usuario -p tu_base_de_datos < schema.sql
   ```
3. Editar `config.php` con tus credenciales de base de datos.
4. Asegurarse de que la carpeta `images/` contiene el logo (`logo.png`).
5. Iniciar el servidor (XAMPP → Apache) y acceder a `http://localhost/la_gran_ruta/login.php`.

## Uso rápido

- **Login** → Credenciales de prueba (usuario: `admin`, contraseña: `admin`).
- Navegar mediante el menú superior.
- En **Inventario** puedes crear, editar y eliminar productos sin recargar la página.
- En **Ventas** registra ventas, el stock se actualiza automáticamente.
- El **Dashboard** muestra métricas en tiempo real y una gráfica de stock.

## Estructura de carpetas

```
la_gran_ruta/
│   index.php            # Punto de entrada (redirecciona al login)
│   login.php            # Autenticación
│   dashboard.php        # Resumen de negocio + gráficos
│   inventario.php       # Gestión de productos
│   ventas.php           # Gestión de ventas
│   nomina.php           # Gestión de empleados
│   config.php           # Conexión PDO
│   schema.sql           # Script de base de datos
│
├───includes/            # Componentes reutilizables
│       header_nav.php   # Menú de navegación con iconos
│
├───css/                # Estilos
│       styles.css       # Diseño premium y responsive
│
├───js/                 # Scripts JavaScript
│       inventario.js    # CRUD de inventario vía AJAX
│       ventas.js        # CRUD de ventas vía AJAX
│
└───images/             # Recursos gráficos (logo, etc.)
```

## Próximos pasos / mejoras

- Implementar **toast notifications** en lugar de `alert()` para una mejor experiencia de usuario.
- Añadir **paginación** y búsqueda avanzada en tablas de inventario y ventas.
- Mejorar la seguridad con **prepared statements** en todas las consultas y sanitización de entrada.
- Integrar **tests unitarios** (PHPUnit) y pruebas de UI automatizadas.

---

*Actualizado el 2025‑12‑02 con todos los cambios realizados hasta la fecha.*
