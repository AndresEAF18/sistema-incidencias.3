<?php  
session_start();  
  
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'usuario') {  
    header("Location: /incidencias/public/index.html");  
    exit();  
}  
  
include_once "../Controllers/detalle_incidente_logica.php";  
  
if (!$detalle) {  
    echo "<script>alert('Incidente no encontrado o acceso denegado.'); window.location.href='historial_incidentes.php';</script>";  
    exit();  
}  
  
// Procesar coordenadas  
$coords = explode(',', $detalle['ubicacion']);  
$lat = trim($coords[0] ?? 0);  
$lng = trim($coords[1] ?? 0);  
  
// Procesar contenido según estado  
$contenidoEstado = '';  
if ($detalle['estado'] == "Pendiente") {  
    $contenidoEstado = '<p><strong>El sistema ha recibido tu reporte, el responsable está revisando los detalles y el análisis de riesgo para asignar unidades.</strong></p>';  
    if (!empty($detalle['comentario'])) {  
        $contenidoEstado .= '<p><strong>Comentario inicial:</strong> ' . htmlspecialchars($detalle['comentario']) . '</p>';  
    }  
} elseif ($detalle['estado'] == "En Proceso") {  
    $contenidoEstado = '<p><i class="fas fa-running"></i> <strong>¡Tu incidente está siendo atendido!</strong></p>';  
    if (!empty($detalle['agente_encargado'])) {  
        $contenidoEstado .= '<p>• <strong>Agente a cargo:</strong> ' . htmlspecialchars($detalle['agente_encargado']) . '</p>';  
    }  
    if (!empty($detalle['unidad_id'])) {  
        $contenidoEstado .= '<p>• <strong>Unidad asignada:</strong> ' . htmlspecialchars($detalle['unidad_id']) . '</p>';  
    }  
    if (!empty($detalle['accionRealizada'])) {  
        $contenidoEstado .= '<p>• <strong>Acciones actuales:</strong> ' . nl2br(htmlspecialchars($detalle['accionRealizada'])) . '</p>';  
    }  
} elseif ($detalle['estado'] == "No Resuelto") {  
    $contenidoEstado = '<p><i class="fas fa-exclamation-triangle"></i> <strong>Atención: El proceso se ha detenido.</strong></p>';  
    $contenidoEstado .= '<p>• <strong>Agente a cargo:</strong> ' . htmlspecialchars($detalle['agente_encargado']) . '</p>';  
    $contenidoEstado .= '<p>• <strong>Unidad asignada:</strong> ' . htmlspecialchars($detalle['unidad_id']) . '</p>';  
    $contenidoEstado .= '<p>• <strong>Motivo del cierre:</strong> ' . htmlspecialchars($detalle['motivoPausa'] ?? 'No especificado') . '</p>';  
    if (!empty($detalle['observacionesFinales'])) {  
        $contenidoEstado .= '<p>• <strong>Detalles adicionales:</strong> ' . nl2br(htmlspecialchars($detalle['observacionesFinales'])) . '</p>';  
    }  
} elseif ($detalle['estado'] == "Resuelto") {  
    $contenidoEstado = '<p><i class="fas fa-check-circle"></i> <strong>Incidente Finalizado con éxito.</strong></p>';  
    $contenidoEstado .= '<p>• <strong>Agente a cargo:</strong> ' . htmlspecialchars($detalle['agente_encargado']) . '</p>';  
    $contenidoEstado .= '<p>• <strong>Unidad asignada:</strong> ' . htmlspecialchars($detalle['unidad_id']) . '</p>';  
    $contenidoEstado .= '<p>• <strong>Resultado final:</strong> ' . nl2br(htmlspecialchars($detalle['resultadoObtenido'])) . '</p>';  
    $contenidoEstado .= '<p>• <strong>Fecha de cierre:</strong> ' . $detalle['fechaCierre'] . '</p>';  
      
    if (!empty($detalle['acta_ruta'])) {  
        $contenidoEstado .= '<div style="margin-top: 15px;">  
            <a href="' . htmlspecialchars($detalle['acta_ruta']) . '" target="_blank" class="btn-acta">  
                📂 Ver Acta de Resolución Final  
            </a>  
        </div>';  
    }  
}  
  
$datos = [  
    'idIncidente' => htmlspecialchars($detalle['idIncidente']),  
    'titulo' => htmlspecialchars($detalle['titulo']),  
    'descripcion' => htmlspecialchars($detalle['descripcion']),  
    'categoria' => htmlspecialchars($detalle['categoria']),  
    'subcategoria' => htmlspecialchars($detalle['subcategoria']),  
    'fechaRegistro' => $detalle['fechaRegistro'],  
    'idPrioridad' => $detalle['idPrioridad'],  
    'textoPrioridad' => ($detalle['idPrioridad']==1?"Alta":($detalle['idPrioridad']==2?"Media":"Baja")),  
    'estado' => strtoupper(htmlspecialchars($detalle['estado'])), // ← Aquí el cambio  
    'responsable' => htmlspecialchars($detalle['responsable'] ?: 'Pendiente'),  
    'lat' => $lat,  
    'lng' => $lng,  
    'contenidoEstado' => $contenidoEstado  
];
  
$template = file_get_contents(__DIR__ . '/../Templates/detalle_incidente.html');  
foreach ($datos as $clave => $valor) {  
    $template = str_replace('{{' . $clave . '}}', $valor, $template);  
}  
  
echo $template;  
?>