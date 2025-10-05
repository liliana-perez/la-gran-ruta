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
 *
 * Detalles de implementación:
 * - La conexión a la BD se realiza con mysqli por compatibilidad local.
 * - Soporta tanto contraseñas hasheadas (password_hash/password_verify) como
 *   contraseñas en texto plano (solo para compatibilidad con datos heredados).
 *
 * Recomendaciones de seguridad:
 * - Use HTTPS, limite intentos de login, y almacene contraseñas con password_hash.
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
// Inicializar variable de error para mostrar en la interfaz si existe
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $clave = $_POST['clave'] ?? '';

    // Consulta segura usando prepared statements
    $stmt = $conn->prepare("SELECT id_usuario, nombre, clave FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verificar la contraseña.
        // Soportamos tanto hash con password_hash como contraseñas en texto plano
        $stored = $row['clave'];
        $valid = false;
        if (!empty($stored) && password_verify($clave, $stored)) {
            $valid = true;
        } elseif ($clave === $stored) {
            // Fallback para bases de datos con contraseñas en texto plano
            $valid = true;
        }

        if ($valid) {
            // Guardar datos en la sesión y redirigir al dashboard
            $_SESSION['user_id'] = $row['id_usuario'];
            $_SESSION['user_name'] = $row['nombre'];
            $stmt->close();
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Clave incorrecta.';
        }
    } else {
        $error = 'Usuario no encontrado.';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="login-container">
        <img src="images/logo.png" alt="Logo" class="logo">
        <form method="POST" action="" class="login-form">
            <h1>Iniciar sesión</h1>
            <?php if (!empty($error)): ?>
                <!-- Mensaje de error accesible y con diseño -->
                <div class="message message-error" role="alert" aria-live="assertive">
                    <span class="message-icon" aria-hidden="true">⚠</span>
                    <div class="message-text"><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="correo">Correo</label>
                <input type="email" name="correo" id="correo" required>
            </div>
            <div class="form-group">
                <label for="clave">Clave</label>
                <input type="password" name="clave" id="clave" required>
            </div>
            <button type="submit" class="btn">Ingresar</button>
        </form>
    </div>
</body>

</html>