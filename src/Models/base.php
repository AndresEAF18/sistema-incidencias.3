<?php
// Configuración de la base de datos
$host = "localhost";
$usuario = "root";
$clave = ""; // Déjalo vacío si usas XAMPP por defecto
$bd = "incidencias"; // Nombre de tu base de datos

// Crear la conexión usando la extensión mysqli
$conexion = new mysqli($host, $usuario, $clave, $bd);

// Verificar si hay errores de conexión
if ($conexion->connect_error) {
    // Si falla, detiene la ejecución y muestra el error
    die("Error crítico de conexión: " . $conexion->connect_error);
}

// CONFIGURACIÓN VITAL: 
// Establecer el conjunto de caracteres a UTF-8 para que las tildes, 
// eñes y caracteres especiales se guarden y muestren correctamente.
$conexion->set_charset("utf8");