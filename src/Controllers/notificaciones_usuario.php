<?php
// 1. CONEXIÓN: Ruta corregida para entrar a Models desde Controllers
include_once "../Models/base.php"; 

// 2. SEGURIDAD: 
// El session_start() NO es necesario aquí porque este archivo 
// se incluye dentro de panel_usuario.php, el cual ya tiene la sesión iniciada.

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'usuario') {
    // Si alguien intenta acceder al script directamente sin sesión
    header("Location: /incidencias/public/index.html");
    exit();
}

$idUsuario = $_SESSION['id'];


// ==========================
// TOTAL DE INCIDENTES
// ==========================
$sqlTotal = "SELECT COUNT(*) AS total FROM incidentes WHERE idUsuario = ?";
$stmt = $conexion->prepare($sqlTotal);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$totalIncidentes = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();


// ==========================
// TOTAL DE INCIDENTES RESUELTOS
// ==========================
$sqlResueltas = "SELECT COUNT(*) AS total FROM incidentes 
                 WHERE idUsuario = ? AND estado = 'Resuelto'";
$stmtRes = $conexion->prepare($sqlResueltas);
$stmtRes->bind_param("i", $idUsuario);
$stmtRes->execute();
$totalResueltas = $stmtRes->get_result()->fetch_assoc()['total'] ?? 0;
$stmtRes->close();


// ==========================
// TOTAL DE INCIDENTES PENDIENTES
// ==========================
$sqlPend = "SELECT COUNT(*) AS total FROM incidentes 
            WHERE idUsuario = ? AND estado = 'Pendiente'";
$stmtPend = $conexion->prepare($sqlPend);
$stmtPend->bind_param("i", $idUsuario);
$stmtPend->execute();
$totalPendientes = $stmtPend->get_result()->fetch_assoc()['total'] ?? 0;
$stmtPend->close();


// ==========================
// INCIDENCIAS POR CATEGORÍA (para la gráfica)
// ==========================
$sqlCat = "SELECT categorias.nombre AS nombreCategoria, COUNT(*) AS total
           FROM incidentes 
           INNER JOIN categorias ON incidentes.idCategoria = categorias.idCategoria
           WHERE incidentes.idUsuario = ?
           GROUP BY incidentes.idCategoria";

$stmtCat = $conexion->prepare($sqlCat);
$stmtCat->bind_param("i", $idUsuario);
$stmtCat->execute();
$resultCat = $stmtCat->get_result();

$categorias = [];
$valoresCategorias = [];

while ($row = $resultCat->fetch_assoc()) {
    $categorias[] = $row['nombreCategoria'];
    $valoresCategorias[] = $row['total'];
}
$stmtCat->close();


// ==========================
// Últimos 3 INCIDENTES RECIENTES
// ==========================
$sqlRecientes = "SELECT titulo, estado, fechaRegistro 
                 FROM incidentes 
                 WHERE idUsuario = ? 
                 ORDER BY fechaRegistro DESC 
                 LIMIT 3";

$stmtRec = $conexion->prepare($sqlRecientes);
$stmtRec->bind_param("i", $idUsuario);
$stmtRec->execute();
$resultRec = $stmtRec->get_result();

$incidentesRecientes = [];
while ($row = $resultRec->fetch_assoc()) {
    $incidentesRecientes[] = $row;
}
$stmtRec->close();


// ==========================
// NOTIFICACIONES (últimas 20)
// ==========================
$sql = "SELECT mensaje, tipo, fecha
        FROM notificaciones
        WHERE idUsuario = ?
        ORDER BY fecha DESC
        LIMIT 20";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$res = $stmt->get_result();

$notificaciones = [];
while ($row = $res->fetch_assoc()) {
    $notificaciones[] = $row;
}
$stmt->close();


// ==========================
// Contar notificaciones NO LEÍDAS
// ==========================

// Contar no-leídas para responsable
$sqlContar = "SELECT COUNT(*) AS total FROM notificaciones WHERE idUsuario = ? AND leida = 0";
$stmtContar = $conexion->prepare($sqlContar);
$stmtContar->bind_param("i", $idUsuario);
$stmtContar->execute();
$resultContar = $stmtContar->get_result();
$rowContar = $resultContar->fetch_assoc();
$notificacionesNoLeidas = $rowContar['total'] ?? 0;
$stmtContar->close();

// Marcar como leídas si se indica
if (!empty($marcarLeidas) && $marcarLeidas === true) {
    $sqlActualizar = "UPDATE notificaciones SET leida = 1 WHERE idUsuario = ?";
    $stmtActualizar = $conexion->prepare($sqlActualizar);
    $stmtActualizar->bind_param("i", $idUsuario);
    $stmtActualizar->execute();
    $stmtActualizar->close();

    $notificacionesNoLeidas = 0;
}


// ==========================
// PORCENTAJE DE RESOLUCIÓN
// ==========================
$porcentajeResueltas = $totalIncidentes > 0 ? round(($totalResueltas / $totalIncidentes) * 100) : 0;


// ==========================
// INCIDENCIAS POR ESTADO (Pendiente, Resuelto, En proceso)
// ==========================
$estados = ['Pendiente', 'Resuelto', 'En proceso'];
$valoresEstado = [];

foreach ($estados as $estado) {
    $sqlEstado = "SELECT COUNT(*) AS total FROM incidentes WHERE idUsuario = ? AND estado = ?";
    $stmtEstado = $conexion->prepare($sqlEstado);
    $stmtEstado->bind_param("is", $idUsuario, $estado);
    $stmtEstado->execute();
    $resultEstado = $stmtEstado->get_result()->fetch_assoc();
    $valoresEstado[] = $resultEstado['total'] ?? 0;
    $stmtEstado->close();
}


// ==========================
// COMPARATIVA MENSUAL DE INCIDENCIAS
// ==========================
$meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
$valoresMes = [];

for ($m = 1; $m <= 12; $m++) {
    $sqlMes = "SELECT COUNT(*) AS total FROM incidentes WHERE idUsuario = ? AND MONTH(fechaRegistro) = ?";
    $stmtMes = $conexion->prepare($sqlMes);
    $stmtMes->bind_param("ii", $idUsuario, $m);
    $stmtMes->execute();
    $resultMes = $stmtMes->get_result()->fetch_assoc();
    $valoresMes[] = $resultMes['total'] ?? 0;
    $stmtMes->close();
}


// ==========================
// Cerrar conexión
// ==========================
$conexion->close();

?>