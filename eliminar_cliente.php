<?php
htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
require_once 'includes/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

session_start();
if (!isset($_SESSION['usuario_id']) || !esAgente($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM clientes WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: dashboard.php");
?>