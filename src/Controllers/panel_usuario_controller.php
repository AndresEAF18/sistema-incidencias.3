<?php  
session_start();  
  
// 1. Validación de sesión  
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'usuario') {  
    header("Location: /incidencias/public/index.html");  
    exit();  
}  
  
// 2. Obtener datos  
include_once "../Models/base.php";  
include_once "../Controllers/notificaciones_usuario.php";  
  
// 3. Procesar incidentes recientes  
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
    $tablaIncidentes = '<tr><td colspan="3">No hay reportes recientes.</td></tr>';  
}  
  
// 4. Preparar todos los datos  
$datos = [  
    'nombre' => htmlspecialchars($_SESSION['nombre']),  
    'notificacionesNoLeidas' => $notificacionesNoLeidas ?? 0,  
    'totalIncidentes' => $totalIncidentes ?? 0,  
    'totalResueltas' => $totalResueltas ?? 0,  
    'totalPendientes' => $totalPendientes ?? 0,  
    'porcentajeResueltas' => $porcentajeResueltas ?? 0,  
    'tablaIncidentes' => $tablaIncidentes,  
    'categorias' => json_encode($categorias ?? []),  
    'valoresCategorias' => json_encode($valoresCategorias ?? []),  
    'meses' => json_encode($meses ?? []),  
    'valoresMes' => json_encode($valoresMes ?? [])  
];  
  
// 5. Cargar y renderizar template  
$template = file_get_contents(__DIR__ . '/../Templates/panel_usuario.html');  
foreach ($datos as $clave => $valor) {  
    $template = str_replace('{{' . $clave . '}}', $valor, $template);  
}  
  
echo $template;  
?>