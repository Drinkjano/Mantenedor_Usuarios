<?php
htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
// Conexión a MySQL (XAMPP)
$servername = "localhost";
$username = "root";
$password = "12345678";
$dbname = "enlinea_telefonica";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Función para validar RUT chileno
function validarRUT($rut) {
    return preg_match('/^(\d{1,3}(?:\.\d{3}){2}-[\dkK])$/', $rut);
}

// Función para validar contraseña
function validarContrasena($contrasena) {
    return (strlen($contrasena) >= 8 && 
            preg_match('/[A-Z]/', $contrasena) && 
            preg_match('/[()$%!"\/&=]/', $contrasena));
}

// Ejemplo de validación al registrar un cliente
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST["rut"];
    $contrasena = $_POST["contrasena"];

    if (!validarRUT($rut)) {
        echo "RUT inválido.";
    } elseif (!validarContrasena($contrasena)) {
        echo "La contraseña debe tener al menos 8 caracteres, una mayúscula y un símbolo.";
    } else {
        // Insertar en la base de datos
        $sql = "INSERT INTO clientes (rut, nombre, direccion, email, telefono, plan, usuario, contrasena) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $rut, $_POST["nombre"], $_POST["direccion"], $_POST["email"], 
                          $_POST["telefono"], $_POST["plan"], $_POST["usuario"], password_hash($contrasena, PASSWORD_DEFAULT));
        
        if ($stmt->execute()) {
            echo "Cliente registrado correctamente.";
        } else {
            echo "Error al registrar: " . $conn->error;
        }
    }
}
?>