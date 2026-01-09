<?php
// 1. Incluimos la conexión (ruta relativa desde Controllers a Models)
include_once "../Models/base.php";

// 2. No iniciamos sesión aquí porque el archivo 'padre' (historial_incidentes.php) ya lo hizo.
// Pero validamos que existan los datos necesarios.
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'usuario') {
    header("Location: /incidencias/public/index.html");
    exit();
}

$idUsuario = $_SESSION['id']; 
$incidentes = []; 

/* ===========================
   FILTROS (Vienen por la URL)
=========================== */
$estado      = $_GET['estado'] ?? '';
$categoria   = $_GET['categoria'] ?? '';
$fechaDesde  = $_GET['fecha_desde'] ?? '';
$fechaHasta  = $_GET['fecha_hasta'] ?? '';

/* ===========================
   CONSULTA BASE (Uso de LEFT JOIN para no perder datos)
=========================== */
$sql = "SELECT 
            i.idIncidente,
            i.titulo,
            c.nombre AS categoria,
            s.nombre AS subcategoria,
            i.idPrioridad,
            i.estado,
            i.fechaRegistro,
            u.nombre AS usuario,
            r.nombre AS responsable
        FROM incidentes i
        INNER JOIN categorias c ON i.idCategoria = c.idCategoria
        INNER JOIN subcategorias s ON i.idSubcategoria = s.idSubcategoria
        INNER JOIN usuarios u ON i.idUsuario = u.idUsuario
        LEFT JOIN responsables r ON i.idResponsable = r.idResponsable
        WHERE i.idUsuario = ?";

$params = [$idUsuario];
$types  = "i";

/* ===========================
   APLICAR FILTROS DINÁMICOS
=========================== */
if ($estado !== '') {
    $sql .= " AND i.estado = ?";
    $params[] = $estado;
    $types .= "s";
}

if ($categoria !== '') {
    $sql .= " AND i.idCategoria = ?";
    $params[] = $categoria;
    $types .= "i";
}

if ($fechaDesde !== '') {
    $sql .= " AND i.fechaRegistro >= ?";
    $params[] = $fechaDesde . " 00:00:00";
    $types .= "s";
}

if ($fechaHasta !== '') {
    $sql .= " AND i.fechaRegistro <= ?";
    $params[] = $fechaHasta . " 23:59:59";
    $types .= "s";
}

$sql .= " ORDER BY i.fechaRegistro DESC";

/* ===========================
   EJECUCIÓN SEGURA
=========================== */
$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

while ($fila = $result->fetch_assoc()) {
    $incidentes[] = $fila;
}
$stmt->close();

/* ===========================
   CARGAR CATEGORÍAS (Para el select del filtro)
=========================== */
$categorias = [];
$resCat = $conexion->query("SELECT idCategoria, nombre FROM categorias");
if($resCat) {
    while ($cat = $resCat->fetch_assoc()) {
        $categorias[] = $cat;
    }
}

