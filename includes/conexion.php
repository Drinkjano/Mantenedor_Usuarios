<?php
// Archivo: includes/conexion.php
// Configuración de conexión a la base de datos

$host = "localhost";
$usuario_db = "root";       // Usuario con privilegios limitados
$password_db = "12345678";    // Contraseña del usuario
$nombre_db = "enlinea_telefonica"; // Nombre de la base de datos

try {
    // 1. Establecer conexión
    $conexion = new mysqli($host, $usuario_db, $password_db, $nombre_db);
    
    // 2. Verificar errores
    if ($conexion->connect_errno) {
        throw new Exception(
            "Error de conexión MySQL: [" . $conexion->connect_errno . "] " . 
            $conexion->connect_error
        );
    }
    
    // 3. Configurar codificación UTF-8
    if (!$conexion->set_charset("utf8mb4")) {
        throw new Exception("Error al configurar charset: " . $conexion->error);
    }
    
    // 4. Configurar zona horaria (opcional)
    $conexion->query("SET time_zone = '-04:00'");
    
} catch (Exception $e) {
    // Registrar error y mostrar mensaje genérico
    error_log("[" . date('Y-m-d H:i:s') . "] Error DB: " . $e->getMessage());
    die("Error en el sistema. Por favor intente más tarde.");
}

// Verificar si password_hash está disponible
if (!function_exists('password_verify')) {
    die("Se requiere PHP 5.5+ para password_hash()");
}


// Función para verificar contraseñas (puede ir en funciones.php)
function verificar_contrasena($contrasena_plana, $hash_almacenado) {
    return password_verify($contrasena_plana, $hash_almacenado);
}
?>