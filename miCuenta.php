<?php
session_start();

// 1. Verificar si hay usuario en sesión
$usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;

if ($usuario === null) {
    // No hay usuario logueado → ir a login
    header('Location: login.php');
    exit;
}

$idUsuario = (int)$usuario['idUsuario']; // asegúrate de guardar esto al hacer login

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$user = 'root';
$pass = 'root';

$cliente = null;
$pedidos = [];
$errorMsg = null;

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Obtener datos del cliente
    $sqlCliente = "SELECT ID_cliente, nombre, apellido, telefono, direccion
                   FROM cliente
                   WHERE ID_usuario = ?";
    $stmtCli = $pdo->prepare($sqlCliente);
    $stmtCli->execute([$idUsuario]);
    $rowCli = $stmtCli->fetch(PDO::FETCH_ASSOC);

    $idCliente = -1;
    if ($rowCli) {
        $idCliente = (int)$rowCli['ID_cliente'];

        $cliente = [
            'idCliente' => $idCliente,
            'nombre'    => $rowCli['nombre'],
            'apellido'  => $rowCli['apellido'],
            'telefono'  => $rowCli['telefono'],
            'direccion' => $rowCli['direccion'],
        ];

        // opcional: mantener nombre en sesión
        $_SESSION['usuario']['nombre'] = $cliente['nombre'];
    }

    // 3. Historial de pedidos
    if ($idCliente !== -1) {
        $sqlPed = "SELECT ID_pedido, ID_cliente, fecha, estado, total
                   FROM pedido
                   WHERE ID_cliente = ?
                   ORDER BY fecha DESC";
        $stmtPed = $pdo->prepare($sqlPed);
        $stmtPed->execute([$idCliente]);
        while ($row = $stmtPed->fetch(PDO::FETCH_ASSOC)) {
            $pedidos[] = [
                'IDpedido' => $row['ID_pedido'],
                'fecha'    => $row['fecha'],
                'estado'   => $row['estado'],
                'total'    => $row['total'],
            ];
        }
    }

} catch (PDOException $e) {
    $errorMsg = "Error de base de datos: " . $e->getMessage();
}

// 4. Guardar datos para que login.php los use
$_SESSION['cliente'] = $cliente;
$_SESSION['pedidos'] = $pedidos;
if ($errorMsg !== null) {
    $_SESSION['login_error'] = $errorMsg;
}

// 5. Mostrar login.php con historial y datos cargados
header('Location: login.php');
exit;
