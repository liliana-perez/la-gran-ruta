<?php

/**
 * login.php
 *
 * Propósito:
 * - Autenticar usuarios mediante un formulario y establecer una sesión válida.
 *
 * Entrada (POST):
 * - correo: string (email)
 * - clave: string (password)
 *
 * Salida / efectos:
 * - En caso de éxito: inicia sesión y redirige a `dashboard.php`.
 * - En caso de error: muestra un mensaje en la misma página.
 */

require_once 'config.php';

// Iniciar sesión para almacenar datos del usuario tras autenticación
session_start();

// Si el usuario ya está autenticado, redirigir al dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Conexión a la base de datos
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}

// Procesar el formulario de login
$error = '';
$correo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $clave = $_POST['clave'] ?? '';

    if ($correo === '' || $clave === '') {
        $error = 'Por favor complete todos los campos.';
    } else {
        // Consulta segura usando prepared statements
        $stmt = $conn->prepare("SELECT id_usuario, nombre, clave FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $hash = $row['clave'];
            $isValid = false;

            // Soporta contraseñas hasheadas y texto plano heredado
            if (password_verify($clave, $hash)) {
                $isValid = true;
            } elseif (hash_equals($hash, $clave)) {
                // comparación segura para texto plano (solo compatibilidad)
                $isValid = true;
            }

            if ($isValid) {
                // Autenticación exitosa: establecer sesión y redirigir
                $_SESSION['user_id'] = $row['id_usuario'];
                $_SESSION['user_name'] = $row['nombre'];
                $stmt->close();
                $conn->close();
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Credenciales incorrectas.';
            }
        } else {
            $error = 'Usuario no encontrado.';
        }

        $stmt->close();
    }
}

// Mostrar mensaje si viene del registro
$registered = (!empty($_GET['registered']) && $_GET['registered'] == 1);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Iniciar sesión - La Gran Ruta</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="login-container">
        <img src="images/logo.png" alt="Logo" class="logo">
        <form method="POST" action="" class="login-form" novalidate>
            <h1>Iniciar sesión</h1>

            <?php if ($registered): ?>
                <div class="message message-success" role="status" aria-live="polite">
                    <span class="message-icon" aria-hidden="true">✔</span>
                    <div class="message-text">Registro exitoso. Por favor inicia sesión.</div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="message message-error" role="alert" aria-live="assertive">
                    <span class="message-icon" aria-hidden="true">⚠</span>
                    <div class="message-text"><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="correo">Correo</label>
                <input type="email" name="correo" id="correo" required value="<?php echo htmlspecialchars($correo); ?>">
            </div>

            <div class="form-group">
                <label for="clave">Clave</label>
                <input type="password" name="clave" id="clave" required>
            </div>

            <button type="submit" class="btn">Ingresar</button>

            <p class="muted">¿No tienes cuenta? <a href="registrarse.php">Regístrate</a></p>
        </form>
    </div>
</body>

</html>