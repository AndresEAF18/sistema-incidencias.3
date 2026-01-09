<?php  
session_start();  
  
// 1. Validación de sesión  
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'responsable') {  
    header("Location: /incidencias/public/index.html");  
    exit();  
}  
  
// 2. Obtener datos del incidente  
include_once "../Controllers/detalle_incidente_logica_responsable.php";  
  
if (!$detalle) {  
    die("Incidente no encontrado.");  
}  
  
// 3. Procesar coordenadas para el mapa  
$coords = explode(',', $detalle['ubicacion']);  
$lat = trim($coords[0] ?? 0);  
$lng = trim($coords[1] ?? 0);  
  

// 4. Lógica de bloqueo según estado  
$estaResuelto = ($detalle['estado'] === 'Resuelto');  
$atributoBloqueo = $estaResuelto ? 'disabled' : '';  
$readonlyBloqueo = $estaResuelto ? 'readonly' : '';  
$forzarSeccionResuelto = $estaResuelto; // Nuevo indicador  


// 5. Preparar datos para el template  
$datos = [  
    'forzarSeccionResuelto' => $forzarSeccionResuelto,  
    'idIncidente' => htmlspecialchars($detalle['idIncidente']),  
    'titulo' => htmlspecialchars($detalle['titulo']),  
    'descripcion' => htmlspecialchars($detalle['descripcion']),  
    'categoria' => htmlspecialchars($detalle['categoria']),  
    'subcategoria' => htmlspecialchars($detalle['subcategoria']),  
    'usuario' => htmlspecialchars($detalle['usuario']),  
    'ubicacion' => htmlspecialchars($detalle['ubicacion']),  
    'lat' => $lat,  
    'lng' => $lng,  
    'idPrioridad' => $detalle['idPrioridad'],  
    'textoPrioridad' => ($detalle['idPrioridad']==1?"ALTA":($detalle['idPrioridad']==2?"MEDIA":"BAJA")),  
    'estado' => htmlspecialchars($detalle['estado']),  
    'atributoBloqueo' => $atributoBloqueo,  
    'readonlyBloqueo' => $readonlyBloqueo,  
    'comentario' => htmlspecialchars($detalle['comentario'] ?? ''),  
    'agente_encargado' => htmlspecialchars($detalle['agente_encargado'] ?? ''),  
    'unidad_id' => htmlspecialchars($detalle['unidad_id'] ?? ''),  
    'accionRealizada' => htmlspecialchars($detalle['accionRealizada'] ?? ''),  
    'motivoPausa' => htmlspecialchars($detalle['motivoPausa'] ?? ''),  
    'observacionesFinales' => htmlspecialchars($detalle['observacionesFinales'] ?? ''),  
    'resultadoObtenido' => htmlspecialchars($detalle['resultadoObtenido'] ?? ''),  
    'tiempoResolucion' => htmlspecialchars($detalle['tiempoResolucion'] ?? ''),  
    'acta_ruta' => htmlspecialchars($detalle['acta_ruta'] ?? ''),  
    'mostrarMensajeBloqueo' => $estaResuelto,  
    'mostrarBotonActualizar' => !$estaResuelto,  
    'mostrarFormReabrir' => $estaResuelto  
];  
  
// 6. Cargar y renderizar template  
$template = file_get_contents(__DIR__ . '/../Templates/detalle_incidente_responsable.html');  
foreach ($datos as $clave => $valor) {  
    if (is_bool($valor)) {  
        $template = str_replace('{{' . $clave . '}}', $valor ? 'true' : 'false', $template);  
    } else {  
        $template = str_replace('{{' . $clave . '}}', $valor, $template);  
    }  
}  
  
echo $template;  
?>