<?php
// dashboardDetails.php
header('Content-Type: application/json; charset=utf-8');

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$tipo = $_GET['tipo'] ?? '';
$result = [];

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($tipo === 'sales') {
        $filtroNombre = $_GET['filtroNombreCliente'] ?? '';
        $filtroEstado = $_GET['filtroEstadoVenta'] ?? '';

        $sql = "SELECT p.ID_pedido, p.ID_cliente, c.nombre AS nombreCliente,
                       p.fecha, p.estado, p.total
                FROM pedido p
                LEFT JOIN cliente c ON p.ID_cliente = c.ID_cliente
                WHERE 1=1";
        $params = [];

        if ($filtroNombre !== '') {
            $sql .= " AND c.nombre LIKE ?";
            $params[] = "%{$filtroNombre}%";
        }
        if ($filtroEstado !== '' && $filtroEstado !== 'Todos') {
            $sql .= " AND p.estado = ?";
            $params[] = $filtroEstado;
        }
        $sql .= " ORDER BY p.ID_pedido DESC LIMIT 50";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'id'            => (int)$row['ID_pedido'],
                'cliente'       => (int)$row['ID_cliente'],
                'nombreCliente' => $row['nombreCliente'],
                'fecha'         => $row['fecha'],
                'estado'        => $row['estado'],
                'total'         => (float)$row['total']
            ];
        }

    } elseif ($tipo === 'products') {
        $filtroNombre = $_GET['filtroNombreProducto'] ?? '';

        $sql = "SELECT ID_producto, nombre, precio, stock
                FROM producto
                WHERE 1=1";
        $params = [];
        if ($filtroNombre !== '') {
            $sql .= " AND nombre LIKE ?";
            $params[] = "%{$filtroNombre}%";
        }
        $sql .= " ORDER BY ID_producto DESC LIMIT 50";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'id'     => (int)$row['ID_producto'],
                'nombre' => $row['nombre'],
                'precio' => (float)$row['precio'],
                'stock'  => (int)$row['stock']
            ];
        }

    } elseif ($tipo === 'clients') {
        $filtroNombre = $_GET['filtroNombreCliente2'] ?? '';

        $sql = "SELECT ID_cliente, nombre, apellido, telefono
                FROM cliente
                WHERE 1=1";
        $params = [];
        if ($filtroNombre !== '') {
            $sql .= " AND nombre LIKE ?";
            $params[] = "%{$filtroNombre}%";
        }
        $sql .= " ORDER BY ID_cliente DESC LIMIT 50";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'id'       => (int)$row['ID_cliente'],
                'nombre'   => $row['nombre'],
                'apellido' => $row['apellido'],
                'telefono' => $row['telefono']
            ];
        }

    } elseif ($tipo === 'administrators') {
        $filtroNombre = $_GET['filtroNombreAdmin'] ?? '';

        $sql = "SELECT ID_admin, nombre, apellido, telefono
                FROM administrador
                WHERE 1=1";
        $params = [];
        if ($filtroNombre !== '') {
            $sql .= " AND nombre LIKE ?";
            $params[] = "%{$filtroNombre}%";
        }
        $sql .= " ORDER BY ID_admin DESC LIMIT 50";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'id'       => (int)$row['ID_admin'],
                'nombre'   => $row['nombre'],
                'apellido' => $row['apellido'],
                'telefono' => $row['telefono']
            ];
        }
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
