<?php
require_once __DIR__ . '/includes/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

session_start();

// Verificar sesión de agente
if (!isset($_SESSION['usuario_id']) || !esAgente($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Inicializar variables
$valores = [
    'rut' => '',
    'nombre' => '',
    'direccion' => '',
    'email' => '',
    'telefono' => '',
    'plan' => 'normal',
    'usuario' => '',
    'tipo_usuario' => ''
];

$errores = [];
$mostrar_formulario = false;

// Procesar selección de tipo de usuario
if (isset($_GET['tipo'])) {
    $valores['tipo_usuario'] = $_GET['tipo'] == 'agente' ? 'agente' : 'cliente';
    $mostrar_formulario = true;
}

// Procesar envío de formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $valores = array_map('trim', $_POST);
    $contrasena = trim($_POST["contrasena"] ?? '');
    $mostrar_formulario = true;

    // Validaciones
    if (empty($valores['rut'])) {
        $errores['rut'] = "El RUT es obligatorio";
    } elseif (!validarRUT($valores['rut'])) {
        $errores['rut'] = "RUT inválido. Formato: 12.345.678-9";
    } else {
        // Verificar si el RUT ya existe
        $sql = "SELECT id FROM clientes WHERE rut = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $valores['rut']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errores['rut'] = "Este RUT ya está registrado";
        }
    }

    // Resto de validaciones...
    if (empty($valores['nombre'])) {
        $errores['nombre'] = "El nombre es obligatorio";
    }

    // Si no hay errores, registrar
    if (empty($errores)) {
        $conexion->begin_transaction();
        try {
            // Insertar en clientes
            $es_agente = ($valores['tipo_usuario'] == 'agente') ? 1 : 0;
            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
            
            $sql_cliente = "INSERT INTO clientes (rut, nombre, direccion, email, telefono, plan, usuario, contrasena, es_agente) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_cliente = $conexion->prepare($sql_cliente);
            $stmt_cliente->bind_param("ssssssssi", 
                $valores['rut'], $valores['nombre'], $valores['direccion'], $valores['email'],
                $valores['telefono'], $valores['plan'], $valores['usuario'], $contrasena_hash, $es_agente);
            
            if (!$stmt_cliente->execute()) {
                throw new Exception("Error al registrar usuario: " . $conexion->error);
            }
            
            // Insertar en roles
            $nuevo_id = $conexion->insert_id;
            $sql_rol = "INSERT INTO roles (tipo, usuario_id) VALUES (?, ?)";
            $stmt_rol = $conexion->prepare($sql_rol);
            $stmt_rol->bind_param("si", $valores['tipo_usuario'], $nuevo_id);
            
            if (!$stmt_rol->execute()) {
                throw new Exception("Error al asignar rol: " . $conexion->error);
            }
            
            $conexion->commit();
            $_SESSION['exito'] = "Usuario ".$valores['tipo_usuario']." registrado correctamente";
            header("Location: dashboard.php");
            exit();
            
        } catch (Exception $e) {
            $conexion->rollback();
            $errores['general'] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrar Nuevo Usuario</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .tipo-usuario { display: flex; justify-content: center; margin-bottom: 30px; }
        .btn-tipo { 
            padding: 15px 30px; 
            margin: 0 10px; 
            font-size: 18px; 
            border: none; 
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-cliente { background-color: #4CAF50; color: white; }
        .btn-agente { background-color: #2196F3; color: white; }
        .btn-tipo:hover { opacity: 0.9; transform: scale(1.05); }
        .formulario { display: <?= $mostrar_formulario ? 'block' : 'none' ?>; }
        .error { color: red; font-size: 0.9em; margin-top: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-submit { 
            background-color: #FF9800; 
            color: white; 
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-submit:hover { background-color: #F57C00; }
        .btn-volver { 
            display: inline-block;
            margin-top: 15px;
            color: #555;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>Registrar Nuevo Usuario</h1>
    
    <?php if (!$mostrar_formulario): ?>
    <div class="tipo-usuario">
        <a href="registrar.php?tipo=cliente" class="btn-tipo btn-cliente">Cliente</a>
        <a href="registrar.php?tipo=agente" class="btn-tipo btn-agente">Agente</a>
    </div>
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
    <?php endif; ?>
    
    <div class="formulario">
        <?php if (!empty($errores['general'])): ?>
            <div class="error" style="margin-bottom: 20px;"><?= htmlspecialchars($errores['general']) ?></div>
        <?php endif; ?>
        
        <?php if ($mostrar_formulario): ?>
        <h2>Registrar nuevo <?= htmlspecialchars($valores['tipo_usuario']) ?></h2>
        <form method="post">
            <input type="hidden" name="tipo_usuario" value="<?= htmlspecialchars($valores['tipo_usuario']) ?>">
            
            <div class="form-group">
                <label for="rut">RUT:</label>
                <input type="text" id="rut" name="rut" value="<?= htmlspecialchars($valores['rut'] ?? '') ?>" 
                       placeholder="12.345.678-9" required>
                <?php if (!empty($errores['rut'])): ?>
                    <div class="error"><?= htmlspecialchars($errores['rut']) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="nombre">Nombre Completo:</label>
                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($valores['nombre'] ?? '') ?>" required>
                <?php if (!empty($errores['nombre'])): ?>
                    <div class="error"><?= htmlspecialchars($errores['nombre']) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($valores['direccion'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($valores['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($valores['telefono'] ?? '') ?>" 
                       pattern="[0-9]{9,15}" title="Solo números (9-15 dígitos)" required>
            </div>
            
            <div class="form-group">
                <label for="plan">Plan:</label>
                <select id="plan" name="plan" required>
                    <option value="normal" <?= ($valores['plan'] ?? '') == 'normal' ? 'selected' : '' ?>>Normal</option>
                    <option value="bueno" <?= ($valores['plan'] ?? '') == 'bueno' ? 'selected' : '' ?>>Bueno</option>
                    <option value="excelente" <?= ($valores['plan'] ?? '') == 'excelente' ? 'selected' : '' ?>>Excelente</option>
                    <option value="oferta" <?= ($valores['plan'] ?? '') == 'oferta' ? 'selected' : '' ?>>Oferta</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="usuario">Nombre de Usuario:</label>
                <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($valores['usuario'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" required>
                <small>Debe tener al menos 8 caracteres, una mayúscula y un símbolo</small>
                <?php if (!empty($errores['contrasena'])): ?>
                    <div class="error"><?= htmlspecialchars($errores['contrasena']) ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn-submit">Registrar Usuario</button>
            <a href="registrar.php" class="btn-volver">← Volver a selección</a>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>