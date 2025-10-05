<?php

/**
 * config.php
 *
 * Configuración central de la aplicación.
 * - Define las credenciales de la base de datos.
 * - Crea y exporta la instancia PDO ($pdo) para su uso en otros scripts.
 *
 * Uso:
 *   require_once 'config.php';
 *   // usar $pdo para consultas preparadas
 *
 * Seguridad / despliegue:
 * - En producción: mover credenciales a variables de entorno o a un almacén seguro.
 * - Evitar commit de este archivo con credenciales reales a repositorios públicos.
 *
 * Variables exportadas:
 * - $pdo (PDO) - instancia de conexión configurada
 */

// Configuración de la base de datos (local / desarrollo)
$host = 'localhost';
$db   = 'la_gran_ruta';
$user = 'web_gran_ruta';
$pass = 'Wtxir1yJw]p23';
$charset = 'utf8mb4';

// DSN y opciones recomendadas
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Crear la conexión PDO (lanzará excepción en caso de error)
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // En desarrollo está bien ver errores, en producción deberías loguearlo y mostrar un mensaje genérico
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
