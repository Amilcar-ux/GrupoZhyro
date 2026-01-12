<?php
// pedidoDetail.php
header('Content-Type: application/json; charset=utf-8');

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$idPedido = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idPedido <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de pedido inválido'], JSON_UNESCAPED_UNICODE);
    exit;
}

$response = [
    'ID_pedido'        => null,
    'fecha'            => null,
    'nombre'           => null,
    'apellido'         => null,
    'telefono'         => null,
    'direccion'        => null,
    'metodo_pago'      => null,
    'estado'           => null,
    'codigo_aprobacion'=> null,
    'imagen_pago'      => null,
    'total'            => 0,
    'productos'        => []
];

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cabecera pedido
    $sqlCab = "SELECT p.ID_pedido, p.fecha, p.estado, p.total,
                      c.nombre, c.apellido, c.telefono, c.direccion,
                      pa.metodo_pago, pa.codigo_yape, pa.comprobante
               FROM pedido p
               JOIN cliente c ON p.ID_cliente = c.ID_cliente
               LEFT JOIN pago pa ON pa.ID_pedido = p.ID_pedido
               WHERE p.ID_pedido = ?";
    $stmt = $pdo->prepare($sqlCab);
    $stmt->execute([$idPedido]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $response['ID_pedido']         = (int)$row['ID_pedido'];
        $response['fecha']             = $row['fecha'];
        $response['nombre']            = $row['nombre'];
        $response['apellido']          = $row['apellido'];
        $response['telefono']          = $row['telefono'];
        $response['direccion']         = $row['direccion'];
        $response['metodo_pago']       = $row['metodo_pago'];
        $response['total']             = (float)$row['total'];
        $response['estado']            = $row['estado'];
        $response['codigo_aprobacion'] = $row['codigo_yape'];
        $response['imagen_pago']       = $row['comprobante'];
    }

    // Detalle productos
    $sqlDet = "SELECT pr.nombre, dp.cantidad, dp.precio_unitario,
                      dp.color, dp.talla, pr.imagen
               FROM detallepedido dp
               JOIN producto pr ON pr.ID_producto = dp.ID_producto
               WHERE dp.ID_pedido = ?";
    $stmt = $pdo->prepare($sqlDet);
    $stmt->execute([$idPedido]);

    $productos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $productos[] = [
            'nombre'        => $row['nombre'],
            'cantidad'      => (int)$row['cantidad'],
            'preciounitario'=> (float)$row['precio_unitario'],
            'color'         => $row['color'],
            'talla'         => $row['talla'],
            'imagen'        => $row['imagen']
        ];
    }
    $response['productos'] = $productos;

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

