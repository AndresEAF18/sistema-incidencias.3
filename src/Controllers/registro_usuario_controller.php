<?php  
session_start();  
  
// 1. Validación de sesión - si ya está logueado, redirigir  
if (isset($_SESSION['id']) && $_SESSION['tipo'] == 'usuario') {  
    header("Location: ../Views/panel_usuario_view.php");  
    exit();  
}  
  
// 2. Preparar datos para el template  
$datos = [  
    'actionForm' => '/incidencias/src/Controllers/registrar_usuario_procesar.php',  
    'linkLogin' => '/incidencias/public/index.html'  
];  
  
// 3. Cargar y renderizar template  
$template = file_get_contents(__DIR__ . '/../Templates/registro_usuario.html');  
foreach ($datos as $clave => $valor) {  
    $template = str_replace('{{' . $clave . '}}', $valor, $template);  
}  
  
echo $template;  
?>