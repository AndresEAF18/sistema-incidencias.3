<?php
session_start();
// 1. Ruta corregida a la base de datos (Models está un nivel arriba)
include_once "../Models/base.php";

/* ==========================
   Validación de sesión
========================== */
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'usuario') {
    header("Location: /incidencias/public/index.html");
    exit();
}

$idUsuario = $_SESSION['id'];

/* ==========================
   Datos del formulario
========================== */
$titulo         = trim($_POST['titulo'] ?? '');
$descripcion    = trim($_POST['descripcion'] ?? '');
$ubicacion      = trim($_POST['ubicacion'] ?? '');
$idCategoria    = intval($_POST['categoria'] ?? 0);
$idSubcategoria = intval($_POST['subcategoria'] ?? 0);
/* ==========================
   Validaciones y Redirección a la Vista de Confirmación
========================== */
if (empty($titulo) || empty($descripcion) || empty($ubicacion) || $idCategoria <= 0 || $idSubcategoria <= 0) {
    // Corregido: apunta a la carpeta Views
    header("Location: ../Views/incidente_confirmado.php?msg=" . urlencode("Por favor completa todos los campos."));
    exit();
}
/* ==========================
   Asignación automática de prioridad
========================== */
switch ($idCategoria) {
    case 1:
    case 4:
        $idPrioridad = 1; // Alta
        break;
    case 2:
    case 3:
        $idPrioridad = 2; // Media
        break;
    case 5:
        $idPrioridad = 3; // Baja
        break;
    default:
        $idPrioridad = 2;
}
/* ==========================
   Asignación automática de responsable
========================== */
$sql_resp = "SELECT idResponsable FROM responsables WHERE idCategoria = ?";
$stmt_resp = $conexion->prepare($sql_resp);
$stmt_resp->bind_param("i", $idCategoria);
$stmt_resp->execute();
$result_resp = $stmt_resp->get_result();

if ($result_resp->num_rows === 0) {
    header("Location: ../Views/incidente_confirmado.php?msg=" . urlencode("No hay responsable asignado a esta categoría."));
    exit();
}

$idResponsable = $result_resp->fetch_assoc()['idResponsable'];

/* ==========================
   Insertar incidente
========================= */
$sql = "INSERT INTO incidentes (
            titulo, descripcion, ubicacion,
            idCategoria, idSubcategoria, idPrioridad,
            idUsuario, idResponsable,
            estado, fechaRegistro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', NOW())";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("sssiiiii", $titulo, $descripcion, $ubicacion, $idCategoria, $idSubcategoria, $idPrioridad, $idUsuario, $idResponsable);

if (!$stmt->execute()) {
    header("Location: ../Views/incidente_confirmado.php?msg=" . urlencode("Error al registrar el incidente en la base de datos."));
    exit();
}

$idIncidente = $stmt->insert_id;

/* ==========================
   Notificación → Responsable
========================== */
$sql_not_resp = "INSERT INTO notificaciones (mensaje, tipo, idResponsable, idIncidente) VALUES (?, ?, ?, ?)";
$mensaje_responsable = "Se te ha asignado un nuevo incidente.";
$tipo_responsable = "Asignación";

$stmt_not_resp = $conexion->prepare($sql_not_resp);
$stmt_not_resp->bind_param("ssii", $mensaje_responsable, $tipo_responsable, $idResponsable, $idIncidente);
$stmt_not_resp->execute();

/* ==========================
   Notificación → Usuario
========================== */
$sql_not_user = "INSERT INTO notificaciones (mensaje, tipo, idUsuario, idIncidente) VALUES (?, ?, ?, ?)";
$mensaje_usuario = "Tu incidente ha sido registrado correctamente.";
$tipo_usuario = "Registro";

$stmt_not_user = $conexion->prepare($sql_not_user);
$stmt_not_user->bind_param("ssii", $mensaje_usuario, $tipo_usuario, $idUsuario, $idIncidente);
$stmt_not_user->execute();

/* ==========================
   Final: Redirección corregida a la Vista
========================== */
header("Location: ../Views/incidente_confirmado.php?msg=" . urlencode("Incidente registrado correctamente"));

// Cerramos recursos
$stmt->close();
$stmt_resp->close();
$conexion->close();
exit();
?>