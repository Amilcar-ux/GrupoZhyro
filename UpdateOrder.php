<?php
// UpdateOrder.php
session_start();

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método no permitido';
    exit;
}

$idPedidoStr = $_POST['ID_pedido'] ?? null;
$estado      = $_POST['estado'] ?? null;

header('Content-Type: text/plain; charset=UTF-8');

if ($idPedidoStr === null || $estado === null || !ctype_digit($idPedidoStr)) {
    http_response_code(400);
    echo 'Datos inválidos';
    exit;
}

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('UPDATE pedido SET estado = :estado WHERE ID_pedido = :id');
    $stmt->execute([
        ':estado' => $estado,
        ':id'     => (int)$idPedidoStr
    ]);

    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo 'OK';
    } else {
        http_response_code(400);
        echo 'No se encontró el pedido';
    }

} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al actualizar';
}
