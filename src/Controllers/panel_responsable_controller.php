<?php  
session_start();  
  
// 1. Validación de sesión  
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'responsable') {  
    header("Location: /incidencias/public/index.html");  
    exit();  
}  
  
// 2. Obtener datos  
include_once "../Models/base.php";  
include_once "../Controllers/notificaciones_res.php";  
  
// 3. Procesar incidentes recientes para la tabla  
$tablaIncidentes = '';  
if (!empty($incidentesRecientes)) {  
    foreach (array_slice($incidentesRecientes, 0, 3) as $incidente) {  
        $tablaIncidentes .= '<tr>';  
        $tablaIncidentes .= '<td>' . htmlspecialchars($incidente['titulo']) . '</td>';  
        $tablaIncidentes .= '<td>' . $incidente['estado'] . '</td>';  
        $tablaIncidentes .= '<td>' . date("d/m/y", strtotime($incidente['fechaRegistro'])) . '</td>';  
        $tablaIncidentes .= '</tr>';  
    }  
} else {  
    $tablaIncidentes = '<tr><td colspan="3">No hay incidentes pendientes.</td></tr>';  
}  
  
// 4. Calcular porcentaje de resolución  
$porcentajeResolucion = 0;  
if ($totalIncidentes > 0) {  
    $porcentajeResolucion = round(($totalResueltas / $totalIncidentes) * 100, 1);  
}  
  
// 5. Preparar todos los datos  
$datos = [  
    'nombre' => htmlspecialchars($_SESSION['nombre']),  
    'notificacionesNoLeidas' => $notificacionesNoLeidas ?? 0,  
    'totalAsignadas' => $totalAsignadas ?? 0,  
    'totalPendientes' => $totalPendientes ?? 0,  
    'totalResueltas' => $totalResueltas ?? 0,  
    'porcentajeResolucion' => $porcentajeResolucion,  
    'tablaIncidentes' => $tablaIncidentes,  
    'meses' => json_encode($meses ?? []),  
    'valoresMes' => json_encode($valoresMes ?? []),  
    'fechas' => json_encode($fechas ?? []),  
    'totales' => json_encode($totales ?? []),  
    'eficienciaGlobal' => $eficienciaGlobal ?? 0  
];  
  
// 6. Cargar y renderizar template  
$template = file_get_contents(__DIR__ . '/../Templates/panel_responsable.html');  
foreach ($datos as $clave => $valor) {  
    $template = str_replace('{{' . $clave . '}}', $valor, $template);  
}  
  
echo $template;  
?>