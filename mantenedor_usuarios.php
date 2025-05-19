<?php
// C:\xampp\htdocs\enlinea\login.php
require_once 'includes/conexion.php';

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Limpiar inputs
    $input_usuario = trim($_POST["usuario"]);
    $input_contrasena = trim($_POST["contrasena"]);
    
    // 2. Consulta preparada (segura contra SQL Injection)
    $sql = "SELECT c.id, c.usuario, c.contrasena, r.tipo 
            FROM clientes c
            LEFT JOIN roles r ON c.id = r.usuario_id
            WHERE c.usuario = ? OR c.email = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $input_usuario, $input_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    // 3. Verificar usuario
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        
        // 4. Verificar contraseña con BCrypt
        if (password_verify($input_contrasena, $usuario['contrasena'])) {
            session_start();
            $_SESSION['usuario_id'] = $usuario['id'];
            
            // Asignar rol (versión compatible con PHP 5.5)
            $rol = isset($usuario['tipo']) ? $usuario['tipo'] : 'cliente';
            $_SESSION['usuario_rol'] = $rol;
            
            // Redirigir según rol
            $redirect = ($rol == 'agente') ? 'dashboard.php' : 'dashboard.php';
            header("Location: " . $redirect);
            exit();
        } else {
            $error = "Contraseña incorrecta";
            error_log("Intento fallido para usuario: $input_usuario");
        }
    } else {
        $error = "Usuario no encontrado";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Iniciar Sesión Mantencion Usuarios</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 0 auto; padding: 20px; }
        .error { color: red; margin-top: 10px; }
        input, button { display: block; width: 100%; padding: 8px; margin: 5px 0; }
    </style>
</head>
<body>
    <h2>Iniciar Sesión</h2>
    <form method="post">
        <input type="text" name="usuario" placeholder="Usuario o Email" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    </form>
</body>
</html>