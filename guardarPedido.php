<?php
session_start();

$usuario = $_SESSION['usuario'] ?? null;
if ($usuario === null) {
    header('Location: login.php');
    exit;
}

$cliente = $_SESSION['cliente'] ?? null;
if ($cliente === null) {
    $cliente = [];
}

$carrito = $_SESSION['carrito'] ?? [];
if (empty($carrito)) {
    header('Location: carrito.php?error=faltan+datos');
    exit;
}

$metodoPago = $_POST['metodoPago'] ?? 'yape';
$codigoYape = $_POST['codigoYape'] ?? null;

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$dbUser = 'root';
$dbPass = 'root';

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1) asegurar cliente en tabla cliente
    $idUsuario = (int)($usuario['idUsuario'] ?? 0);
    $idCliente = buscarClientePorUsuario($pdo, $idUsuario);

    if ($idCliente === 0) {
        $idCliente = crearCliente($pdo, $usuario, $cliente);
    }

    // 2) calcular totales
    $subtotal = 0;
    foreach ($carrito as $item) {
        $precio   = isset($item['precio'])   ? (float)$item['precio']   : 0.0;
        $cantidad = isset($item['cantidad']) ? (int)$item['cantidad']   : 1;
        $subtotal += $precio * $cantidad;
    }
    $impuestos = 0.0;
    $costoEnvio = isset($_SESSION['costoEnvio']) ? (float)$_SESSION['costoEnvio'] : 0.0;
    $total = $subtotal + $impuestos + $costoEnvio;

    // 3) subir comprobante (si hay)
    $nombreArchivo = null;
    if (!empty($_FILES['comprobante']['name']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['comprobante']['tmp_name'];
        $uploadsDir = __DIR__ . '/comprobantes';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }
        // puedes sacar extensión real con pathinfo si quieres
        $nombreArchivo = 'comprobante_' . time() . '.jpg';
        move_uploaded_file($tmpName, $uploadsDir . DIRECTORY_SEPARATOR . $nombreArchivo);
    }

    // 4) insertar pedido + envío + detalle + pago
    $codigoUnico = 'PED-' . time();

    $pdo->beginTransaction();

    // pedido
    $sqlPedido = "INSERT INTO pedido (codigo_unico, ID_cliente, fecha, estado, total)
                  VALUES (?, ?, NOW(), 'Pendiente', ?)";
    $stmt = $pdo->prepare($sqlPedido);
    $stmt->execute([$codigoUnico, $idCliente, $total]);
    $idPedido = (int)$pdo->lastInsertId();

    // envío
    $direccionEnvio = $cliente['direccion'] ?? '';
    $sqlEnvio = "INSERT INTO envío (ID_pedido, direccion_envio) VALUES (?, ?)";
    $stmtEnv = $pdo->prepare($sqlEnvio);
    $stmtEnv->execute([$idPedido, $direccionEnvio]);

    // detalle
    $sqlDet = "INSERT INTO detallepedido
               (ID_pedido, ID_producto, cantidad, precio_unitario, color, talla)
               VALUES (?, ?, ?, ?, ?, ?)";
    $stmtDet = $pdo->prepare($sqlDet);

    foreach ($carrito as $item) {
        $idProducto = (int)($item['idProducto'] ?? 0);
        $cantidad   = (int)($item['cantidad'] ?? 1);
        $precioUnit = (float)($item['precio'] ?? 0);
        $color      = $item['color'] ?? null;
        $talla      = $item['talla'] ?? null;

        $stmtDet->execute([$idPedido, $idProducto, $cantidad, $precioUnit, $color, $talla]);
    }

    // pago
    $sqlPago = "INSERT INTO pago
       (ID_pedido, metodo_pago, estado, monto, codigo_yape, comprobante, fecha_pago)
       VALUES (?, ?, 'Pendiente', ?, ?, ?, NOW())";
    $stmtPago = $pdo->prepare($sqlPago);
    $stmtPago->execute([
        $idPedido,
        $metodoPago,
        $total,
        $metodoPago === 'yape' ? $codigoYape : null,
        $nombreArchivo
    ]);

    $pdo->commit();

    // limpiar carrito
    unset($_SESSION['carrito']);

    // redirigir a boleta
    header('Location: boleta.php?idPedido=' . $idPedido);
    exit;

} catch (Exception $e) {
    if (!empty($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // en producción pon una página de error bonita
    die('Error al guardar pedido: ' . $e->getMessage());
}

/* ---------- funciones auxiliares ---------- */

function buscarClientePorUsuario(PDO $pdo, int $idUsuario): int {
    if ($idUsuario <= 0) return 0;
    $sql = "SELECT ID_cliente FROM cliente WHERE ID_usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idUsuario]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['ID_cliente'] : 0;
}

function crearCliente(PDO $pdo, array $usuario, array $cliente): int {
    $sql = "INSERT INTO cliente (ID_usuario, nombre, apellido, telefono, direccion, provincia)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    $idUsuario = (int)($usuario['idUsuario'] ?? 0);
    $nombreU   = $usuario['nombre'] ?? '';
    $parts     = explode(' ', $nombreU, 2);
    $nombre    = $parts[0] ?? '';
    $apellido  = $parts[1] ?? '';

    $telefono  = $usuario['telefono']  ?? ($cliente['telefono']  ?? '');
    $direccion = $usuario['direccion'] ?? ($cliente['direccion'] ?? '');
    $provincia = $cliente['provincia'] ?? '';

    $stmt->execute([$idUsuario, $nombre, $apellido, $telefono, $direccion, $provincia]);

    return (int)$pdo->lastInsertId();
}
