<?php  
session_start();  
  
// 1. Obtener mensaje de la URL  
$mensaje = $_GET['msg'] ?? 'Incidente procesado';  
$mensajeDecodificado = htmlspecialchars(urldecode($mensaje));  
  
// 2. Determinar tipo de mensaje basado en el contenido  
$tipoMensaje = 'info';  
if (strpos(strtolower($mensajeDecodificado), 'error') !== false ||   
    strpos(strtolower($mensajeDecodificado), 'incorrecta') !== false) {  
    $tipoMensaje = 'error';  
} elseif (strpos(strtolower($mensajeDecodificado), 'correctamente') !== false ||   
          strpos(strtolower($mensajeDecodificado), 'éxito') !== false) {  
    $tipoMensaje = 'success';  
}  
  
// 3. Preparar datos para el template  
$datos = [  
    'mensaje' => $mensajeDecodificado,  
    'tipoMensaje' => $tipoMensaje  
];  
  
// 4. Cargar y renderizar template  
$template = file_get_contents(__DIR__ . '/../Templates/incidente_confirmado.html');  
foreach ($datos as $clave => $valor) {  
    $template = str_replace('{{' . $clave . '}}', $valor, $template);  
}  
  
echo $template;  
?>