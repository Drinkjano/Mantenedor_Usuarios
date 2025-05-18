<?php
// C:\xampp\htdocs\enlinea\editar.php
require_once 'includes/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener ID del cliente a editar (según rol)
if (esAgente($_SESSION['usuario_id']) && isset($_GET['id'])) {
    $cliente_id = intval($_GET['id']); // Editar cualquier cliente (agente)
} else {
    $cliente_id = intval($_SESSION['usuario_id']); // Editar propio perfil (cliente)
}

// Obtener datos del cliente
$sql = "SELECT * FROM clientes WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$resultado = $stmt->get_result();
$cliente = $resultado->fetch_assoc();

// Procesar formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $direccion = trim($_POST["direccion"]);
    $telefono = trim($_POST["telefono"]);
    $plan = trim($_POST["plan"]);
    
    $sql = "UPDATE clientes SET nombre = ?, direccion = ?, telefono = ?, plan = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssi", $nombre, $direccion, $telefono, $plan, $cliente_id);
    
    if ($stmt->execute()) {
        $mensaje = "Datos actualizados correctamente";
        // Actualizar datos locales
        $cliente['nombre'] = $nombre;
        $cliente['direccion'] = $direccion;
        $cliente['telefono'] = $telefono;
        $cliente['plan'] = $plan;
    } else {
        $error = "Error al actualizar: " . $conexion->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Datos</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        input, select, button { display: block; width: 100%; padding: 8px; margin: 5px 0 15px; }
    </style>
</head>
<body>
    <h1>Editar Mis Datos</h1>
    
    <?php if (isset($mensaje)): ?>
        <p class="success"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="post">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
        
        <label>Dirección:</label>
        <input type="text" name="direccion" value="<?php echo htmlspecialchars($cliente['direccion']); ?>" required>
        
        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>" required>
        
        <label>Plan:</label>
        <select name="plan" required>
            <option value="normal" <?php echo ($cliente['plan'] == 'normal') ? 'selected' : ''; ?>>Normal</option>
            <option value="bueno" <?php echo ($cliente['plan'] == 'bueno') ? 'selected' : ''; ?>>Bueno</option>
            <option value="excelente" <?php echo ($cliente['plan'] == 'excelente') ? 'selected' : ''; ?>>Excelente</option>
            <option value="oferta" <?php echo ($cliente['plan'] == 'oferta') ? 'selected' : ''; ?>>Oferta</option>
        </select>
        
        <button type="submit">Guardar Cambios</button>
    </form>
    
    <div style="margin: 25px 0; text-align: center;">
     <a href="dashboard.php" style="
        display: inline-block;
        padding: 12px 24px;
        background-color: #4e73df;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    ">
        <i class="fas fa-arrow-left"></i> Volver al Panel de Control
     </a>
    </div>


</body>
</html>