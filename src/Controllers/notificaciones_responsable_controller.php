<?php  
session_start();  
  
// 1. Validación de sesión  
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'responsable') {  
    header("Location: /incidencias/public/index.html");  
    exit();  
}  
  
// 2. Indicar que las notificaciones se marquen como leídas  
$marcarLeidas = true;  
  
// 3. Obtener datos del controller de notificaciones  
include_once "../Controllers/notificaciones_res.php";  
  
// 4. Preparar datos para el template  
$datos = [  
    'notificacionesNoLeidas' => $notificacionesNoLeidas ?? 0  
];  
  
// 5. Procesar lista de notificaciones  
$listaNotificaciones = '';  
if (empty($notificaciones)) {  
    $listaNotificaciones = '<li class="vacio">No tienes notificaciones por el momento.</li>';  
} else {  
    foreach ($notificaciones as $noti) {  
        $fechaFormateada = date("d/m/Y H:i", strtotime($noti['fecha']));  
        $mensajeEscapado = htmlspecialchars($noti['mensaje']);  
        $tipoEscapado = htmlspecialchars($noti['tipo']);  
          
        $listaNotificaciones .= '<li class="item-notificacion">';  
        $listaNotificaciones .= '<div class="header-noti">';  
        $listaNotificaciones .= '<span class="fecha">' . $fechaFormateada . '</span>';  
        $listaNotificaciones .= '<span class="tipo-badge ' . strtolower($noti['tipo']) . '">';  
        $listaNotificaciones .= $tipoEscapado;  
        $listaNotificaciones .= '</span>';  
        $listaNotificaciones .= '</div>';  
        $listaNotificaciones .= '<div class="mensaje-noti">';  
        $listaNotificaciones .= $mensajeEscapado;  
        $listaNotificaciones .= '</div>';  
        $listaNotificaciones .= '</li>';  
    }  
}  
  
$datos['listaNotificaciones'] = $listaNotificaciones;  
  
// 6. Cargar y renderizar template  
$template = file_get_contents(__DIR__ . '/../Templates/notificaciones_responsable.html');  
foreach ($datos as $clave => $valor) {  
    $template = str_replace('{{' . $clave . '}}', $valor, $template);  
}  
  
echo $template;  
?>