<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

$idParam = $_POST['id'] ?? null;

try {
    if ($idParam === null || trim($idParam) === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'ID de producto inválido'
        ]);
        exit;
    }

    if (!ctype_digit($idParam)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => 'Formato de ID inválido'
        ]);
        exit;
    }

    $idProducto = (int)$idParam;

    if (!isset($_SESSION['favoritos']) || !is_array($_SESSION['favoritos'])) {
        $_SESSION['favoritos'] = [];
    }

    $favoritos = $_SESSION['favoritos'];
    $exito = false;

    if (!in_array($idProducto, $favoritos, true)) {
        $favoritos[] = $idProducto;
        $exito = true;
    }

    $_SESSION['favoritos'] = $favoritos;

    http_response_code(200);
    echo json_encode(['success' => $exito]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error interno del servidor'
    ]);
    exit;
}
