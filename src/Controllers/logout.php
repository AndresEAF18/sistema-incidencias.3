<?php
session_start();

// Destruye todas las variables de sesión
$_SESSION = [];

// Si se desea destruir la cookie de sesión también
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Cierra y elimina la sesión del servidor
session_destroy();

// Redirige al login usando la ruta absoluta infalible
header("Location: /incidencias/public/index.html");
exit();
?>