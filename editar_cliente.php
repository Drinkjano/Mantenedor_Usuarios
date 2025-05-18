<?php
// C:\xampp\htdocs\enlinea\editar_cliente.php

require_once __DIR__ . '/includes/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

session_start();

// 1. Verificar que el usuario es agente y está logueado
if (!isset($_SESSION['usuario_id']) || !esAgente($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Obtener el ID del cliente a editar
if (!isset($_GET['id'])) {
    die("ID de cliente no especificado");
}

$cliente_id = intval($_GET['id']);

// 3. Obtener datos del cliente
$sql = "SELECT * FROM clientes WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Cliente no encontrado");
}

$cliente = $resultado->fetch_assoc();

// 4. Procesar el formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $direccion = trim($_POST["direccion"]);
    $telefono = trim($_POST["telefono"]);
    $plan = trim($_POST["plan"]);
    $estatus = trim($_POST["estatus"]);
  

    // Validación del teléfono
    if (!preg_match('/^[0-9]{9,15}$/', $telefono)) {
        $error = "El teléfono debe contener solo números (9-15 dígitos)";
    } else {
        $sql = "UPDATE clientes SET 
                nombre = ?, 
                direccion = ?, 
                telefono = ?, 
                plan = ?,
                estatus = ?
                WHERE id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssi", $nombre, $direccion, $telefono, $plan, $estatus, $cliente_id);
        
        if ($stmt->execute()) {
            $mensaje = "Datos del cliente actualizados correctamente";
            // Actualizar datos locales para mostrar
            $cliente['nombre'] = $nombre;
            $cliente['direccion'] = $direccion;
            $cliente['telefono'] = $telefono;
            $cliente['plan'] = $plan;
            $cliente['estatus'] = $estatus;
         } else {
            $error = "Error al actualizar: " . $conexion->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Cliente</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        input, select, button { 
            display: block; 
            width: 100%; 
            padding: 8px; 
            margin: 5px 0 15px; 
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <h1>Editar Cliente</h1>
    
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
            <option value="normal" <?= $cliente['plan'] == 'normal' ? 'selected' : '' ?>>Normal</option>
            <option value="bueno" <?= $cliente['plan'] == 'bueno' ? 'selected' : '' ?>>Bueno</option>
            <option value="excelente" <?= $cliente['plan'] == 'excelente' ? 'selected' : '' ?>>Excelente</option>
            <option value="oferta" <?= $cliente['plan'] == 'oferta' ? 'selected' : '' ?>>Oferta</option>
        </select>
        
        <label>Estatus:</label>
        <select name="estatus" required>
            <option value="Activo" <?= $cliente['estatus'] == 'Activo' ? 'selected' : '' ?>>Activo</option>
            <option value="Inactivo" <?= $cliente['estatus'] == 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>

               
        <button type="submit">Guardar Cambios</button>
    </form>
    
    <p><a href="dashboard.php">Volver al panel de agente</a></p>
</body>
</html>