<?php
$host = 'localhost';
$db = 'login_db';
$user = 'root';
$pass = '';  // Cambia si tienes contraseña configurada en MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("No se pudo conectar a la base de datos: " . $e->getMessage());
    echo "No se pudo conectar a la base de datos.";
}
?>
