

<?php
session_start();

// 1. CONEXIÓN: Usamos ruta relativa porque PHP lo maneja mejor internamente
// Estamos en Controllers/, subimos uno y entramos a Models
include_once "../Models/base.php"; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    // VALIDAR DOMINIO
    $dominioPermitido = "@hermanoscristianos.com";
    if (!str_ends_with($correo, $dominioPermitido)) {
        // RUTA ABSOLUTA para el navegador
        header("Location: /incidencias/src/Views/mensaje.php?msg=" . urlencode("El correo debe ser $dominioPermitido"));
        exit();
    }

    // FUNCIÓN DE LOGUEO
    function procesarLogin($fila, $tabla, $conexion, $correo, $contrasena) {
        // Verificar bloqueo
        if (!empty($fila['bloqueado_hasta']) && strtotime($fila['bloqueado_hasta']) > time()) {
            $restante = date("H:i:s", strtotime($fila['bloqueado_hasta']) - time());
            header("Location: /incidencias/src/Views/mensaje.php?msg=" . urlencode("Bloqueado. Intente en $restante"));
            exit();
        }

        // Verificar contraseña
        if (password_verify($contrasena, $fila['contrasena'])) {
            // Reiniciar intentos
            $sqlReset = "UPDATE $tabla SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE correo = ?";
            $stmtReset = $conexion->prepare($sqlReset);
            $stmtReset->bind_param("s", $correo);
            $stmtReset->execute();

            session_regenerate_id(true);
            $_SESSION['nombre'] = $fila['nombre'];

            if ($tabla === "usuarios") {
                $_SESSION['id'] = $fila['idUsuario'];
                $_SESSION['tipo'] = 'usuario';
                // Redirige al panel en Views
                header("Location: /incidencias/src/Views/panel_usuario.php");
            } else {
                $_SESSION['id'] = $fila['idResponsable'];
                $_SESSION['tipo'] = 'responsable';
                header("Location: /incidencias/src/Views/panel_responsable.php");
            }
            exit();
        }

        // Si falla: aumentar intentos
        $nuevosIntentos = $fila['intentos_fallidos'] + 1;
        if ($nuevosIntentos >= 5) {
            $bloqueo = date("Y-m-d H:i:s", strtotime("+2 minutes"));
            $sqlBlock = "UPDATE $tabla SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE correo = ?";
            $stmtBlock = $conexion->prepare($sqlBlock);
            $stmtBlock->bind_param("iss", $nuevosIntentos, $bloqueo, $correo);
            $stmtBlock->execute();
            header("Location: /incidencias/src/Views/mensaje.php?msg=" . urlencode("Cuenta bloqueada por 2 min."));
            exit();
        }

        $sqlIntento = "UPDATE $tabla SET intentos_fallidos = ? WHERE correo = ?";
        $stmtIntento = $conexion->prepare($sqlIntento);
        $stmtIntento->bind_param("is", $nuevosIntentos, $correo);
        $stmtIntento->execute();
        header("Location: /incidencias/src/Views/mensaje.php?msg=" . urlencode("Clave incorrecta. Intento $nuevosIntentos de 5"));
        exit();
    }

    // BUSCAR EN TABLAS
    $sqlUsuario = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($sqlUsuario);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resU = $stmt->get_result();

    if ($resU->num_rows > 0) {
        procesarLogin($resU->fetch_assoc(), "usuarios", $conexion, $correo, $contrasena);
    }

    $sqlResp = "SELECT * FROM responsables WHERE correo = ?";
    $stmt = $conexion->prepare($sqlResp);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resR = $stmt->get_result();

    if ($resR->num_rows > 0) {
        procesarLogin($resR->fetch_assoc(), "responsables", $conexion, $correo, $contrasena);
    }

    header("Location: /incidencias/src/Views/mensaje.php?msg=" . urlencode("Usuario no existe"));
    exit();

} else {
    header("Location: /incidencias/public/index.html");
    exit();
}