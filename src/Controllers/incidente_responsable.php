<?php
// 1. Ruta corregida: Sube un nivel para encontrar la conexión en Models
include_once "../Models/base.php"; 

// Aseguramos que la sesión esté activa (el ID viene del panel que incluye este archivo)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$idResp = $_SESSION['id'] ?? null;

// Inicializamos el array para que la vista no de error si no hay resultados
$incidentes = [];

if ($idResp) {
    // ==========================
    // Captura de Filtros (GET)
    // ==========================
    $estado = $_GET['estado'] ?? '';
    $fechaDesde = $_GET['fecha_desde'] ?? '';
    $fechaHasta = $_GET['fecha_hasta'] ?? '';

    // ==========================
    // Consulta base con JOINs
    // ==========================
    $sql = "SELECT 
                i.idIncidente,
                i.titulo,
                c.nombre AS categoria,
                s.nombre AS subcategoria,
                i.idPrioridad AS prioridad,
                i.estado,
                i.fechaRegistro,
                u.nombre AS usuario
            FROM incidentes i
            INNER JOIN categorias c ON i.idCategoria = c.idCategoria
            INNER JOIN subcategorias s ON i.idSubcategoria = s.idSubcategoria
            INNER JOIN usuarios u ON i.idUsuario = u.idUsuario
            WHERE i.idResponsable = ?";

    $tipos = "i";
    $parametros = [$idResp];

    // ==========================
    // Aplicación dinámica de filtros
    // ==========================
    if (!empty($estado)) {
        $sql .= " AND i.estado = ?";
        $tipos .= "s";
        $parametros[] = $estado;
    }

    if (!empty($fechaDesde)) {
        $sql .= " AND DATE(i.fechaRegistro) >= ?";
        $tipos .= "s";
        $parametros[] = $fechaDesde;
    }

    if (!empty($fechaHasta)) {
        $sql .= " AND DATE(i.fechaRegistro) <= ?";
        $tipos .= "s";
        $parametros[] = $fechaHasta;
    }

    // Ordenar por los más recientes primero
    $sql .= " ORDER BY i.fechaRegistro DESC";

    // ==========================
    // Ejecución segura
    // ==========================
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param($tipos, ...$parametros);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($fila = $result->fetch_assoc()) {
            $incidentes[] = $fila;
        }
        $stmt->close();
    }
}

?>