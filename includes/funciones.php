<?php
// C:\xampp\htdocs\enlinea\includes\funciones.php

function esAgente($usuario_id) {
    // Asegurarse que $conexion esté disponible
    if (!isset($GLOBALS['conexion'])) {
        require_once 'conexion.php';
    }
    
    $conexion = $GLOBALS['conexion'];
    
    $sql = "SELECT tipo FROM roles WHERE usuario_id = ? AND tipo = 'agente'";
    
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows === 1;
    } else {
        error_log("Error preparando consulta: " . $conexion->error);
        return false;
    }
}

function validarRUT($rut) {
    return preg_match('/^(\d{1,3}(?:\.\d{3}){2}-[\dkK])$/', $rut);
}

function validarContrasena($contrasena) {
    return (strlen($contrasena) >= 8 && 
            preg_match('/[A-Z]/', $contrasena) && 
            preg_match('/[()$%!"\/&=]/', $contrasena));
}