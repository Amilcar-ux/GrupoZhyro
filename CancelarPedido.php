<?php
// CancelarPedido.php
session_start();

header('Content-Type: text/plain; charset=UTF-8');

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método no permitido';
    exit;
}

$idPedidoStr = $_POST['ID_pedido'] ?? null;
if ($idPedidoStr === null || !ctype_digit($idPedidoStr)) {
    http_response_code(400);
    echo 'ID de pedido inválido';
    exit;
}
$idPedido = (int)$idPedidoStr;

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("UPDATE pedido SET estado = 'Cancelado' WHERE ID_pedido = :id");
    $stmt->execute([':id' => $idPedido]);

    if ($stmt->rowCount() > 0) {
        echo 'Pedido cancelado correctamente';
    } else {
        http_response_code(404);
        echo 'Pedido no encontrado';
    }

} catch (Exception $e) {
    http_response_code(500);
    echo 'Error del servidor: ' . $e->getMessage();
}
