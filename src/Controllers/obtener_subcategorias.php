<?php
include_once "../Models/base.php";

$idCategoria = $_GET['idCategoria'] ?? '';

if (!empty($idCategoria)) {
    $sql = "SELECT idSubcategoria, nombre FROM subcategorias WHERE idCategoria = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idCategoria);
    $stmt->execute();
    $result = $stmt->get_result();

    $subcategorias = [];
    while ($row = $result->fetch_assoc()) {
        $subcategorias[] = $row;
    }

    // Importante: Decirle al navegador que esto es un JSON
    header('Content-Type: application/json');
    echo json_encode($subcategorias);
} else {
    echo json_encode([]);
}