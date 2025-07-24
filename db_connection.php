<?php
$servername = "localhost"; // Cambia esto si tu servidor es diferente
$username = "tu_usuario"; // Cambia esto por tu usuario de MySQL
$password = "tu_contraseña"; // Cambia esto por tu contraseña de MySQL
$dbname = "ecommerce_db"; // Nombre de la base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
