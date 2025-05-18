<?php
// C:\xampp\htdocs\enlinea\dashboard.php
//require_once 'includes/conexion.php';
//require_once 'funciones.php';
//
//session_start();

// C:\xampp\htdocs\enlinea\dashboard.php

// Incluir archivos con rutas absolutas
require_once __DIR__ . '/includes/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}


// Redirigir si no hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$es_agente = esAgente($usuario_id);

// Obtener datos del usuario actual
$sql_usuario = "SELECT * FROM clientes WHERE id = ?";
$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $usuario_id);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();
$usuario = $result_usuario->fetch_assoc();

// Si es agente, obtener lista de todos los clientes
if ($es_agente) {
    $sql_clientes = "SELECT * FROM clientes";
    $result_clientes = $conexion->query($sql_clientes);
    $clientes = $result_clientes->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - En Línea</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
        .btn-danger {
            background-color: #f44336;
        }
        .btn-secondary {
            background-color: #2196F3;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?></h1>
            <nav>
                <a href="editar.php" class="btn">Editar mis datos</a>
                <?php if ($es_agente): ?>
                    <a href="dashboard.php" class="btn">Panel de Agente</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
            </nav>
        </header>

        <div class="card">
            <h2>Mis Datos</h2>
            <p><strong>RUT:</strong> <?php echo htmlspecialchars($usuario['rut']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario['telefono']); ?></p>
            <p><strong>Dirección:</strong> <?php echo htmlspecialchars($usuario['direccion']); ?></p>
            <p><strong>Plan:</strong> <?php echo ucfirst(htmlspecialchars($usuario['plan'])); ?></p>
            <p><strong>Estado:</strong> <?php echo htmlspecialchars($usuario['estatus']); ?></p>
        </div>

    <?php if ($es_agente): ?>
       <div class="card">
       <h2>Gestión de Clientes</h2>
         <!-- Agrega este botón arriba de la tabla -->
           <a href="registrar.php" class="btn" style="margin-bottom: 20px;">Nuevo Usuario</a>
            <table>
                <thead>
                    <tr>
                        <th>RUT</th>
                        <th>Nombre</th>
                        <th>Direccion</th>
                        <th>Email</th>
                        <th>Telefono</th>
                        <th>Plan</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cliente['rut']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($cliente['plan'])); ?></td>
                        <td><?php echo htmlspecialchars($cliente['estatus']); ?></td>
                        <td style="display: flex; flex-direction: column; gap: 5px;">
                             <a href="editar_cliente.php?id=<?php echo $cliente['id']; ?>" class="btn btn-secondary">Editar</a>
                             <a href="eliminar_cliente.php?id=<?php echo $cliente['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>