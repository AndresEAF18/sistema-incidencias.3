<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
include_once "../Models/base.php"; 

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'responsable') {
    header("Location: /incidencias/public/index.html");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    header("Location: ../Views/panel_responsable.php");
    exit();
}

// --- PARTE A: PROCESAR ACTUALIZACIÓN O REAPERTURA (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $esReapertura = (isset($_POST['accion']) && $_POST['accion'] === 'reabrir');
    $estado       = trim($_POST['estado']);
    $agente       = trim($_POST['agente_encargado'] ?? '');
    $unidad       = trim($_POST['unidad_id'] ?? '');
    $analisis     = trim($_POST['analisisRiesgo'] ?? '');
    $accionR      = trim($_POST['accionRealizada'] ?? '');
    $motivoP      = trim($_POST['motivoPausa'] ?? '');
    $resultado    = trim($_POST['resultadoObtenido'] ?? '');
    $tiempo       = trim($_POST['tiempoResolucion'] ?? '');
    $observaciones= trim($_POST['observacionesFinales'] ?? '');
    $comentario   = trim($_POST['comentario'] ?? '');
    $fechaRean    = !empty($_POST['fechaReanudacion']) ? $_POST['fechaReanudacion'] : null;

    $rutaArchivo = null;
    if (isset($_FILES['acta_documento']) && $_FILES['acta_documento']['error'] === 0) {
        $dir_fisico = "C:/xampp/htdocs/incidencias/uploads/actas/";
        if (!is_dir($dir_fisico)) mkdir($dir_fisico, 0777, true);
        $ext = pathinfo($_FILES['acta_documento']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = "acta_" . $id . "_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['acta_documento']['tmp_name'], $dir_fisico . $nombreArchivo)) {
            $rutaArchivo = "/incidencias/uploads/actas/" . $nombreArchivo;
        }
    }

    // Lógica de borrado físico si es reapertura
    $sql_borrar_acta = "";
    if ($esReapertura) {
        $stmtB = $conexion->prepare("SELECT acta_ruta FROM incidentes WHERE idIncidente = ?");
        $stmtB->bind_param("i", $id);
        $stmtB->execute();
        $resB = $stmtB->get_result()->fetch_assoc();
        if (!empty($resB['acta_ruta'])) {
            $f = "C:/xampp/htdocs" . $resB['acta_ruta'];
            if (file_exists($f)) unlink($f);
        }
        $sql_borrar_acta = ", acta_ruta = NULL";
    }

    $sql_update_acta = $rutaArchivo ? ", acta_ruta = ?" : "";
    
    if ($esReapertura) {
        $sql_cierre = ", fechaCierre = NULL";
        $mensajeNoti = " Tu incidente #$id a sido REABIERTO. El acta anterior ha sido anulada.";
        $tipoNoti = "Reapertura";
    } else {
        $sql_cierre = ($estado === "Resuelto") ? ", fechaCierre = NOW()" : "";
        $mensajeNoti = "Tu incidente #$id cambió a: $estado.";
        $tipoNoti = $estado;
    }

    $sqlUpd = "UPDATE incidentes SET 
                estado = ?, agente_encargado = ?, unidad_id = ?, 
                analisisRiesgo = ?, accionRealizada = ?, motivoPausa = ?,
                fechaReanudacion = ?, resultadoObtenido = ?, tiempoResolucion = ?, 
                observacionesFinales = ?, comentario = ? 
                $sql_update_acta $sql_borrar_acta $sql_cierre
              WHERE idIncidente = ?";

    $stmt = $conexion->prepare($sqlUpd);
    if ($rutaArchivo) {
        $stmt->bind_param("ssssssssssssi", $estado, $agente, $unidad, $analisis, $accionR, $motivoP, $fechaRean, $resultado, $tiempo, $observaciones, $comentario, $rutaArchivo, $id);
    } else {
        $stmt->bind_param("sssssssssssi", $estado, $agente, $unidad, $analisis, $accionR, $motivoP, $fechaRean, $resultado, $tiempo, $observaciones, $comentario, $id);
    }
    $stmt->execute();

    // Notificación al creador
    $stmtU = $conexion->prepare("SELECT idUsuario FROM incidentes WHERE idIncidente = ?");
    $stmtU->bind_param("i", $id);
    $stmtU->execute();
    $idUs = $stmtU->get_result()->fetch_assoc()['idUsuario'];
    
    $stmtN = $conexion->prepare("INSERT INTO notificaciones (idIncidente, idUsuario, mensaje, tipo, leida, fecha) VALUES (?, ?, ?, ?, 0, NOW())");
    $stmtN->bind_param("iiss", $id, $idUs, $mensajeNoti, $tipoNoti);
    $stmtN->execute();

    header("Location: ../Views/detalle_incidente_responsable.php?id=$id&msj=ok");
    exit();
}

// --- PARTE B: CONSULTA DE DATOS (SIEMPRE SE EJECUTA PARA LA VISTA) ---
$sqlDet = "SELECT i.*, c.nombre AS categoria, s.nombre AS subcategoria, 
                  u.nombre AS usuario, r.nombre AS responsable
           FROM incidentes i
           LEFT JOIN categorias c ON i.idCategoria = c.idCategoria
           LEFT JOIN subcategorias s ON i.idSubcategoria = s.idSubcategoria
           LEFT JOIN usuarios u ON i.idUsuario = u.idUsuario
           LEFT JOIN responsables r ON i.idResponsable = r.idResponsable
           WHERE i.idIncidente = ?";

$stmtD = $conexion->prepare($sqlDet);
$stmtD->bind_param("i", $id);
$stmtD->execute();
$detalle = $stmtD->get_result()->fetch_assoc();

if (!$detalle) {
    die("Incidente no encontrado.");
}