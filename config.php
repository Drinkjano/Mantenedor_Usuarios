<?php
// C:\xampp\htdocs\enlinea\config.php

// Configuración base
define('ROOT_PATH', __DIR__);
define('INCLUDES_PATH', ROOT_PATH . '/includes');

// Función para incluir archivos de manera segura
function requireSafe($relativePath) {
    $absolutePath = INCLUDES_PATH . '/' . ltrim($relativePath, '/');
    if (!file_exists($absolutePath)) {
        die("Archivo requerido no encontrado: " . htmlspecialchars($relativePath));
    }
    require_once $absolutePath;
}
?>