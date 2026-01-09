<?php
// 1. Incluimos la conexión con la ruta corregida
include_once "../Models/base.php";

// 2. Validación de sesión (ya iniciada en la Vista)
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'usuario') {
    header("Location: /incidencias/public/index.html");
    exit();
}

$idUsuarioSesion = $_SESSION['id'];

// 3. Validar ID del incidente enviado por la URL
if (!isset($_GET['id'])) {
    header("Location: historial_incidentes.php?msg=" . urlencode("ID de incidente no proporcionado."));
    exit();
}

$id = intval($_GET['id']);

// 4. CONSULTA DETALLADA ACTUALIZADA
// Añadimos todos los campos nuevos de la tabla para que la vista pueda usarlos
$sql = "SELECT 
            i.idIncidente,
            i.titulo,
            i.descripcion,
            i.ubicacion,
            c.nombre AS categoria,
            s.nombre AS subcategoria,
            i.idPrioridad,
            i.analisisRiesgo,
            i.estado,
            i.fechaRegistro,
            i.fechaCierre,
            i.agente_encargado,
            i.unidad_id,
            i.comentario,
            i.accionRealizada,
            i.motivoPausa,
            i.fechaReanudacion,
            i.resultadoObtenido,
            i.observacionesFinales,
            i.tiempoResolucion,
            i.acta_ruta,
            u.nombre AS usuario,
            r.nombre AS responsable
        FROM incidentes i
        INNER JOIN categorias c ON i.idCategoria = c.idCategoria
        LEFT JOIN subcategorias s ON s.idSubcategoria = i.idSubcategoria
        INNER JOIN usuarios u ON i.idUsuario = u.idUsuario
        LEFT JOIN responsables r ON i.idResponsable = r.idResponsable
        WHERE i.idIncidente = ? AND i.idUsuario = ?";

$stmt = $conexion->prepare($sql);
// Seguridad: i.idUsuario = ? asegura que un usuario no pueda ver incidentes de otros cambiando el ID en la URL
$stmt->bind_param("ii", $id, $idUsuarioSesion);
$stmt->execute();
$result = $stmt->get_result();

$detalle = $result->fetch_assoc();

$stmt->close();
?>