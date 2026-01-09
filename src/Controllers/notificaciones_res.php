<?php
// --- asegurar sesión y conexión ---
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// 1. Ruta corregida: Sube un nivel para entrar a Models
include_once "../Models/base.php"; 

// Inicializar variables para evitar warnings en la Vista
$totalAsignadas = 0;
$totalResueltas = 0;
$totalPendientes = 0;
$porcentajeResolucion = 0;

// Asegurar que existe id en sesión
$idResponsable = $_SESSION['id'] ?? null;

if ($idResponsable) {
    /* ==========================================
       CONTEO DE ESTADOS (TOTALES)
    ========================================== */
    $sql = "SELECT estado FROM incidentes WHERE idResponsable = ?";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $idResponsable);
        $stmt->execute();
        $stmt->bind_result($estado);
        while ($stmt->fetch()) {
            $totalAsignadas++;
            // Validamos variaciones de texto
            if (in_array(strtolower($estado), ['resuelto', 'resuelta'])) {
                $totalResueltas++;
            } elseif (strtolower($estado) === 'pendiente') {
                $totalPendientes++;
            }
        }
        $stmt->close();
    }

    $totalIncidentes = $totalAsignadas;

    /* ==========================================
       ÚLTIMOS 3 INCIDENTES RECIENTES
    ========================================== */
    $sqlRecientes = "SELECT titulo, estado, fechaRegistro 
                     FROM incidentes 
                     WHERE idResponsable = ? 
                     ORDER BY fechaRegistro DESC 
                     LIMIT 3";
    $stmtRec = $conexion->prepare($sqlRecientes);
    $stmtRec->bind_param("i", $idResponsable);
    $stmtRec->execute();
    $resultRec = $stmtRec->get_result();
    $incidentesRecientes = [];
    while ($row = $resultRec->fetch_assoc()) {
        $incidentesRecientes[] = $row;
    }
    $stmtRec->close();

    /* ==========================================
       GRÁFICA: ÚLTIMOS 7 DÍAS
    ========================================== */
    $sqlDias = "SELECT DATE(fechaRegistro) AS dia, COUNT(*) AS total
                FROM incidentes
                WHERE idResponsable = ?
                AND DATE(fechaRegistro) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
                GROUP BY DATE(fechaRegistro)
                ORDER BY dia ASC";
    $stmtDias = $conexion->prepare($sqlDias);
    $stmtDias->bind_param("i", $idResponsable);
    $stmtDias->execute();
    $resultDias = $stmtDias->get_result();

    $fechas = [];
    $totales = [];
    for ($i = 6; $i >= 0; $i--) {
        $fechas[] = date('Y-m-d', strtotime("-$i day"));
        $totales[] = 0; 
    }
    while ($row = $resultDias->fetch_assoc()) {
        $index = array_search($row['dia'], $fechas);
        if ($index !== false) $totales[$index] = (int)$row['total'];
    }
    $stmtDias->close();

    /* ==========================================
       GRÁFICA: COMPARATIVA MENSUAL
    ========================================== */
    $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    $valoresMes = [];
    for ($m = 1; $m <= 12; $m++) {
        $sqlMes = "SELECT COUNT(*) AS total FROM incidentes WHERE idResponsable = ? AND MONTH(fechaRegistro) = ?";
        $stmtMes = $conexion->prepare($sqlMes);
        $stmtMes->bind_param("ii", $idResponsable, $m);
        $stmtMes->execute();
        $valoresMes[] = $stmtMes->get_result()->fetch_assoc()['total'] ?? 0;
        $stmtMes->close();
    }

// ==========================
// EFICIENCIA GLOBAL DE RESOLUCIÓN
// ==========================
$eficienciaGlobal = 0;
$tiempoOptimo = 48; // horas

$sqlGlobal = "
SELECT AVG(
    GREATEST(TIMESTAMPDIFF(MINUTE, fechaRegistro, fechaCierre), 1)
) AS promedioMinutos
FROM incidentes
WHERE idResponsable = ?
AND estado = 'Resuelto'
AND fechaCierre IS NOT NULL
";

$stmtGlobal = $conexion->prepare($sqlGlobal);
$stmtGlobal->bind_param("i", $idResponsable);
$stmtGlobal->execute();
$resultGlobal = $stmtGlobal->get_result()->fetch_assoc();

$promedioMinutos = $resultGlobal['promedioMinutos'] ?? 0;

if ($promedioMinutos > 0) {
    $promedioHoras = $promedioMinutos / 60;
    $eficienciaCalculada = ($tiempoOptimo / $promedioHoras) * 100;
    $eficienciaGlobal = round(min($eficienciaCalculada, 100), 1);
} else {
    $eficienciaGlobal = 0;
}

$stmtGlobal->close();

    /* ==========================================
       NOTIFICACIONES (CONTEO Y LISTADO)
    ========================================== */
    // Contar no leídas
    $stmtContar = $conexion->prepare("SELECT COUNT(*) AS total FROM notificaciones WHERE idResponsable = ? AND leida = 0");
    $stmtContar->bind_param("i", $idResponsable);
    $stmtContar->execute();
    $notificacionesNoLeidas = $stmtContar->get_result()->fetch_assoc()['total'] ?? 0;
    $stmtContar->close();

    // Marcar como leídas (si la vista lo solicita)
    if (!empty($marcarLeidas) && $marcarLeidas === true) {
        $stmtAct = $conexion->prepare("UPDATE notificaciones SET leida = 1 WHERE idResponsable = ?");
        $stmtAct->bind_param("i", $idResponsable);
        $stmtAct->execute();
        $stmtAct->close();
        $notificacionesNoLeidas = 0;
    }

    // Obtener las 20 más recientes
    $stmtNot = $conexion->prepare("SELECT mensaje, tipo, fecha, leida FROM notificaciones WHERE idResponsable = ? ORDER BY fecha DESC LIMIT 20");
    $stmtNot->bind_param("i", $idResponsable);
    $stmtNot->execute();
    $resultNot = $stmtNot->get_result();
    $notificaciones = [];
    while ($row = $resultNot->fetch_assoc()) {
        $notificaciones[] = $row;
    }
    $stmtNot->close();
}

// Cerramos conexión al final del procesamiento
$conexion->close();