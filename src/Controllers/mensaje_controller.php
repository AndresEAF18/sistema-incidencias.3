<?php  
session_start();  
  
// Obtener y procesar mensaje  
$mensaje = $_GET['msg'] ?? 'Mensaje no disponible';  
$mensajeDecodificado = htmlspecialchars(urldecode($mensaje));  
  
// Preparar datos  
$datos = [  
    'mensaje' => $mensajeDecodificado  
];  
  
// Renderizar template  
$template = file_get_contents(__DIR__ . '/../Templates/mensaje.html');  
foreach ($datos as $clave => $valor) {  
    $template = str_replace('{{' . $clave . '}}', $valor, $template);  
}  
  
echo $template;  
?>