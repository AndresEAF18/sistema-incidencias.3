<?php
session_start();
// Conexión: Sube un nivel a src/ y entra a Models
include_once "../Models/base.php"; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Obtener y limpiar datos
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $contrasena = trim($_POST['contrasena']);

    // 1. VALIDAR DOMINIO PERMITIDO
    $dominioPermitido = "@hermanoscristianos.com";
    if (!str_ends_with($correo, $dominioPermitido)) {
        // Ruta absoluta al mensaje de error
        header("Location: /incidencias/src/Views/mensaje.php?msg=" . urlencode("El correo debe ser del dominio $dominioPermitido"));
        exit();
    }

    // 2. VALIDAR SI EL CORREO YA EXISTE
    $sql = "SELECT idUsuario FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: /incidencias/src/Views/mensaje.php?msg=" . urlencode("El correo ya está registrado."));
        exit();
    }

    // 3. HASHEAR CONTRASEÑA (Seguridad)
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    // 4. INSERTAR USUARIO
    $sqlInsert = "INSERT INTO usuarios (nombre, correo, contrasena) VALUES (?, ?, ?)";
    $stmtInsert = $conexion->prepare($sqlInsert);
    $stmtInsert->bind_param("sss", $nombre, $correo, $hash);

    if ($stmtInsert->execute()) {
        // Éxito: Enviar al mensaje confirmando el registro
        header("Location: /incidencias/src/Views/mensaje.php?msg=" . urlencode("Registro exitoso. Ya puedes iniciar sesión."));
        exit();
    } else {
        // Error en la base de datos
        header("Location: /incidencias/src/Views/mensaje.php?msg=" . urlencode("Error al registrar: " . $conexion->error));
        exit();
    }

} else {
    // Si intentan entrar directo al archivo, al index
    header("Location: /incidencias/public/index.html");
    exit();
}