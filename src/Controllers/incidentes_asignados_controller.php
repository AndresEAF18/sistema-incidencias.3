<?php  
session_start();  
  
// 1. Validación de sesión  
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'responsable') {  
    header("Location: /incidencias/public/index.html");  
    exit();  
}  
  
// 2. Obtener datos de incidentes asignados  
include_once "../Controllers/incidente_responsable.php";  
  
// 3. Preparar datos para el template  
$datos = [  
    'estadoSeleccionado' => $_GET['estado'] ?? '',  
    'fechaDesde' => $_GET['fecha_desde'] ?? '',  
    'fechaHasta' => $_GET['fecha_hasta'] ?? ''  
];  
  
// 4. Procesar tabla de incidentes (convertir array a HTML)  
$tablaIncidentes = '';  
if (empty($incidentes)) {  
    $tablaIncidentes = '<tr><td colspan="9" style="text-align: center;">No se encontraron incidentes con los filtros seleccionados.</td></tr>';  
} else {  
    foreach ($incidentes as $fila) {  
        $tablaIncidentes .= '<tr>';  
        $tablaIncidentes .= '<td>' . $fila['idIncidente'] . '</td>';  
        $tablaIncidentes .= '<td><strong>' . htmlspecialchars($fila['titulo']) . '</strong></td>';  
        $tablaIncidentes .= '<td>' . $fila['categoria'] . '</td>';  
        $tablaIncidentes .= '<td>' . $fila['subcategoria'] . '</td>';  
        $tablaIncidentes .= '<td><span class="prioridad-' . strtolower($fila['prioridad']) . '">' . $fila['prioridad'] . '</span></td>';  
        $tablaIncidentes .= '<td><span class="badge-estado">' . $fila['estado'] . '</span></td>';  
        $tablaIncidentes .= '<td>' . htmlspecialchars($fila['usuario']) . '</td>';  
        $tablaIncidentes .= '<td>' . date("d/m/Y", strtotime($fila['fechaRegistro'])) . '</td>';  
        $tablaIncidentes .= '<td><a href="detalle_incidente_responsable.php?id=' . $fila['idIncidente'] . '" class="btn-detalle"><span class="mas-icono">+</span> Gestionar</a></td>';  
        $tablaIncidentes .= '</tr>';  
    }  
}  
  
$datos['tablaIncidentes'] = $tablaIncidentes;  
  
// 5. Cargar y renderizar template  
$template = file_get_contents(__DIR__ . '/../Templates/incidentes_asignados.html');  
foreach ($datos as $clave => $valor) {  
    $template = str_replace('{{' . $clave . '}}', $valor, $template);  
}  
  
echo $template;  
?>