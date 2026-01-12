<?php
// dashboardData.php
header('Content-Type: application/json; charset=utf-8');

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$data = [
    'administrators' => 0,
    'clients'        => 0,
    'providers'      => 0,
    'categories'     => 0,
    'products'       => 0,
    'sales'          => 0
];

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data['administrators'] = (int)$pdo->query("SELECT COUNT(*) FROM administrador")->fetchColumn();
    $data['clients']        = (int)$pdo->query("SELECT COUNT(*) FROM cliente")->fetchColumn();
    $data['providers']      = (int)$pdo->query("SELECT COUNT(*) FROM proveedor")->fetchColumn();
    // Si tienes tabla de categorías, cámbiala aquí; de lo contrario queda en 0
    $data['products']       = (int)$pdo->query("SELECT COUNT(*) FROM producto")->fetchColumn();
    $data['sales']          = (int)$pdo->query("SELECT COUNT(*) FROM pedido")->fetchColumn();

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
