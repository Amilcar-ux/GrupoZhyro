<?php
// notifications.php
header('Content-Type: application/json; charset=utf-8');

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$response = [
    'count'         => 0,
    'notifications' => []
];

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT p.ID_pedido, p.fecha, p.total, c.nombre, c.apellido
            FROM pedido p
            JOIN cliente c ON p.ID_cliente = c.ID_cliente
            WHERE p.estado = 'Pendiente'
            ORDER BY p.fecha DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $response['notifications'][] = [
            'id'            => (int)$row['ID_pedido'],
            'fecha'         => $row['fecha'],
            'total'         => (float)$row['total'],
            'nombreCliente' => $row['nombre'] . ' ' . $row['apellido']
        ];
    }

    $response['count'] = count($response['notifications']);

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en el servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
