<?php
htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
// C:\xampp\htdocs\enlinea\logout.php
session_start();
session_destroy();
header("Location: login.php");
exit();
?>