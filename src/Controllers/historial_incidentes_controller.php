<?php  
session_start();  
  
// 1. Validación de sesión  
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'usuario') {  
    header("Location: /incidencias/public/index.html");  
    exit();  
}  
  
// 2. Obtener datos de incidentes  
include_once "../Controllers/incidencias_usuarios.php";  
  
// 3. Preparar datos para el template  
$datos = [  
    'estadoSeleccionado' => $_GET['estado'] ?? '',  
    'categoriaSeleccionada' => $_GET['categoria'] ?? '',  
    'fechaDesde' => $_GET['fecha_desde'] ?? '',  
    'fechaHasta' => $_GET['fecha_hasta'] ?? ''  
];  
  
// 4. Procesar opciones de categorías  
$optionsCategorias = '';  
foreach ($categorias as $cat) {  
    $selected = ($datos['categoriaSeleccionada'] == $cat['idCategoria']) ? 'selected' : '';  
    $optionsCategorias .= "<option value='{$cat['idCategoria']}' {$selected}>";  
    $optionsCategorias .= htmlspecialchars($cat['nombre']);  
    $optionsCategorias .= "</option>";  
}  
  
// 5. Procesar tabla de incidentes  
$tablaIncidentes = '';  
if (empty($incidentes)) {  
    $tablaIncidentes = '<tr><td colspan="8" class="sin-datos">No se encontraron incidentes con estos filtros.</td></tr>';  
} else {  
    foreach ($incidentes as $fila) {  
        $tablaIncidentes .= '<tr>';  
        $tablaIncidentes .= '<td>' . $fila['idIncidente'] . '</td>';  
        $tablaIncidentes .= '<td>' . htmlspecialchars($fila['titulo']) . '</td>';  
        $tablaIncidentes .= '<td>' . htmlspecialchars($fila['categoria']) . '</td>';  
        $tablaIncidentes .= '<td>' . htmlspecialchars($fila['subcategoria']) . '</td>';  
        $tablaIncidentes .= '<td><span class="estado-' . strtolower(str_replace(' ', '-', $fila['estado'])) . '">';  
        $tablaIncidentes .= $fila['estado'] . '</span></td>';  
        $tablaIncidentes .= '<td>' . $fila['fechaRegistro'] . '</td>';  
        $tablaIncidentes .= '<td>' . htmlspecialchars($fila['responsable'] ?? 'Sin asignar') . '</td>';  
        $tablaIncidentes .= '<td><a href="detalle_incidente.php?id=' . $fila['idIncidente'] . '" class="btn-detalle">Ver Detalles</a></td>';  
        $tablaIncidentes .= '</tr>';  
    }  
}  
  
// 6. Agregar datos procesados  
$datos['optionsCategorias'] = $optionsCategorias;  
$datos['tablaIncidentes'] = $tablaIncidentes;  
  
// 7. Cargar y renderizar template  
$template = file_get_contents(__DIR__ . '/../Templates/historial_incidentes.html');  
foreach ($datos as $clave => $valor) {  
    $template = str_replace('{{' . $clave . '}}', $valor, $template);  
}  
  
echo $template;  
?>