<?php

/**
 * registrarse.php
 *
 * Permite a un usuario crear una cuenta.
 * - Valida nombre, correo, contraseña y confirmación.
 * - Verifica que el correo no exista.
 * - Hashea la contraseña con password_hash antes de insertar en la tabla `usuarios`.
 *
 * Requiere: config.php con $host, $user, $pass, $db
 */

require_once 'config.php';

$error = '';
$success = '';
$nombre = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $clave = $_POST['clave'] ?? '';
    $clave2 = $_POST['clave2'] ?? '';

    // Validaciones básicas
    if ($nombre === '' || $correo === '' || $clave === '' || $clave2 === '') {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo inválido.';
    } elseif ($clave !== $clave2) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($clave) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        // Conectar a la BD y verificar existencia
        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            $error = 'Error de conexión a la base de datos.';
        } else {
            $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = 'Ya existe una cuenta con ese correo.';
                $stmt->close();
                $conn->close();
            } else {
                $stmt->close();
                $hash = password_hash($clave, PASSWORD_DEFAULT);
                $ins = $conn->prepare("INSERT INTO usuarios (nombre, correo, clave) VALUES (?, ?, ?)");
                $ins->bind_param("sss", $nombre, $correo, $hash);
                if ($ins->execute()) {
                    // Redirigir al login para que el usuario inicie sesión
                    $ins->close();
                    $conn->close();
                    header('Location: login.php?registered=1');
                    exit;
                } else {
                    $error = 'Error al registrar. Intente nuevamente.';
                }
                $ins->close();
                $conn->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Registrarse - La Gran Ruta</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="login-container">
        <img src="images/logo.png" alt="Logo" class="logo">
        <form method="POST" action="" class="login-form" novalidate>
            <h1>Crear cuenta</h1>

            <?php if (!empty($error)): ?>
                <div class="message message-error" role="alert" aria-live="assertive">
                    <span class="message-icon" aria-hidden="true">⚠</span>
                    <div class="message-text"><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php elseif (!empty($success)): ?>
                <div class="message message-success" role="status" aria-live="polite">
                    <span class="message-icon" aria-hidden="true">✔</span>
                    <div class="message-text"><?php echo $success; // contiene HTML intencional para enlace 
                                                ?></div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" required value="<?php echo htmlspecialchars($nombre); ?>">
            </div>

            <div class="form-group">
                <label for="correo">Correo</label>
                <input type="email" name="correo" id="correo" required value="<?php echo htmlspecialchars($correo); ?>">
            </div>

            <div class="form-group">
                <label for="clave">Contraseña</label>
                <input type="password" name="clave" id="clave" required>
            </div>

            <div class="form-group">
                <label for="clave2">Confirmar contraseña</label>
                <input type="password" name="clave2" id="clave2" required>
            </div>

            <button type="submit" class="btn">Registrar</button>

            <p class="muted">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
        </form>
    </div>
</body>

</html>