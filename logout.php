<?php

/**
 * logout.php
 *
 * Muestra una página de confirmación estilizada para cerrar sesión (GET)
 * y procesa el cierre de sesión (POST).
 *
 * GET:
 *  - Muestra UI con dos opciones: "Cerrar sesión" (envía POST) y "Cancelar" (redirige a dashboard.php).
 * POST:
 *  - Verifica token CSRF simple en sesión, destruye la sesión y redirige a login.php?loggedout=1
 */

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF simple
    $token = $_POST['logout_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['logout_token'] ?? '', $token)) {
        // token inválido -> redirigir por seguridad
        header('Location: dashboard.php');
        exit;
    }

    // Limpiar todas las variables de sesión
    $_SESSION = array();

    // Destruir la cookie de sesión en el navegador
    if (session_id() !== '' || isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }

    // Destruir la sesión en el servidor
    session_destroy();

    // Redirigir al login con indicador de salida
    header('Location: login.php?loggedout=1');
    exit;
}

// Si llegamos por GET, generar token CSRF y mostrar confirmación
if (empty($_SESSION['logout_token'])) {
    // generar token seguro
    $_SESSION['logout_token'] = bin2hex(random_bytes(16));
}
$token = $_SESSION['logout_token'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Cerrar sesión - Confirmar</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Ajustes rápidos locales para el panel de confirmación */
        .logout-card {
            max-width: 520px;
            margin: 80px auto;
            padding: 22px;
            border-radius: 12px;
            background: linear-gradient(180deg, #ffffff, #fbfbff);
            box-shadow: 0 18px 50px rgba(12, 40, 80, 0.09);
            text-align: center;
        }

        .logout-title {
            font-size: 1.25rem;
            margin-bottom: 10px;
            color: #123;
        }

        .logout-desc {
            color: #556;
            margin-bottom: 18px;
        }

        .logout-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 8px;
        }

        .btn-logout {
            background: linear-gradient(90deg, #ef5350, #e53935);
            color: #fff;
            padding: 10px 16px;
            border-radius: 10px;
            border: none;
            font-weight: 700;
        }

        .btn-cancel {
            background: transparent;
            border: 1px solid #d7d7d7;
            color: #333;
            padding: 10px 14px;
            border-radius: 10px;
        }

        @media (max-width:520px) {
            .logout-card {
                margin: 40px 18px;
            }

            .logout-actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header class="header" style="margin-top:28px; margin-bottom:18px;">
            <div class="header-brand">
                <img src="images/logo.png" alt="Logo" class="logo">
                <h1 class="title">LA GRAN RUTA</h1>
            </div>
        </header>

        <main class="main-content" role="main">
            <section class="logout-card" role="region" aria-labelledby="logout-heading">
                <h2 id="logout-heading" class="logout-title">¿Deseas cerrar tu sesión?</h2>
                <p class="logout-desc">Al cerrar sesión se cerrará tu acceso a la aplicación. Puedes volver a iniciar sesión en cualquier momento.</p>

                <form method="POST" action="" style="margin:0;">
                    <input type="hidden" name="logout_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>">
                    <div class="logout-actions">
                        <button type="submit" class="btn-logout" id="confirm-logout">Sí, cerrar sesión</button>
                        <a href="dashboard.php" class="btn-cancel" id="cancel-logout">No, volver al panel</a>
                    </div>
                </form>

                <p style="margin-top:14px; color:#7a7a7a; font-size:0.9rem;">Si cierras sesión en un equipo público, asegúrate de cerrar también el navegador.</p>
            </section>
        </main>

        <footer class="footer" style="margin-top:28px;">
            <p class="rights">© La Gran Ruta</p>
        </footer>
    </div>

    <script>
        // mejora UX: confirmar acción con teclado y prevenir doble envío
        (function() {
            var form = document.querySelector('form');
            var btn = document.getElementById('confirm-logout');
            if (!form || !btn) return;
            form.addEventListener('submit', function(e) {
                // prevenir doble click
                btn.disabled = true;
                btn.innerText = 'Cerrando...';
            });

            // atajo ESC para volver al dashboard (accesibilidad)
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    window.location.href = 'dashboard.php';
                }
            });
        })();
    </script>
</body>

</html>