<?php

/**
 * logout.php
 *
 * Propósito:
 * - Finalizar la sesión del usuario (limpiar $_SESSION y cookie de sesión)
 * - Redirigir al usuario a la página de login (`login.php`).
 *
 * Efectos:
 * - Borra la sesión del lado servidor y cliente.
 */

session_start();

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión en el navegador
if (session_id() !== '' || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destruir la sesión en el servidor
session_destroy();
header('Location: login.php');
exit;
