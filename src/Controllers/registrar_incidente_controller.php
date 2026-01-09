<?php  
session_start();  
  
// 1. Validación de sesión  
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'usuario') {  
    header("Location: /incidencias/public/index.html");  
    exit();  
}  
  
// 2. Obtener categorías  
include_once "../Models/base.php";  
$sql = "SELECT idCategoria, nombre FROM categorias";  
$result = $conexion->query($sql);  
  
// 3. Preparar opciones de categorías  
$optionsCategorias = '';  
while ($fila = $result->fetch_assoc()) {  
    $optionsCategorias .= "<option value='{$fila['idCategoria']}'>{$fila['nombre']}</option>";  
}  
  
// 4. Preparar datos para el template  
$datos = [  
    'optionsCategorias' => $optionsCategorias,  
    'nombreUsuario' => htmlspecialchars($_SESSION['nombre'])  
];  
  
// 5. Cargar y renderizar template  
$template = file_get_contents(__DIR__ . '/../Templates/registrar_incidente.html');  
foreach ($datos as $clave => $valor) {  
    $template = str_replace('{{' . $clave . '}}', $valor, $template);  
}  
  
echo $template;  
?>