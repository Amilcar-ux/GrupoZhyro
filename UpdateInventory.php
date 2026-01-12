<?php
// UpdateInventory.php
session_start();

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inventory.php');
    exit;
}

$idProducto     = $_POST['ID_producto']   ?? null;
$stockStr       = $_POST['stock']         ?? null;
$stockMinimoStr = $_POST['stock_minimo']  ?? null;
$precioStr      = $_POST['precio']        ?? null;
$estado         = $_POST['estado']        ?? null;

if ($idProducto === null || $stockStr === null || $stockMinimoStr === null || 
    $precioStr === null || $estado === null) {
    $_SESSION['inventory_error'] = 'Faltan datos para actualizar producto.';
    header('Location: inventory.php');
    exit;
}

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    $sql = "UPDATE producto 
            SET stock = :stock,
                stock_minimo = :stock_minimo,
                precio = :precio,
                estado = :estado,
                fecha_actualizacion_stock = NOW()
            WHERE ID_producto = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':stock'        => (int)$stockStr,
        ':stock_minimo' => (int)$stockMinimoStr,
        ':precio'       => (float)$precioStr,
        ':estado'       => $estado,
        ':id'           => (int)$idProducto
    ]);

    if ($stmt->rowCount() > 0) {
        $pdo->commit();
        header('Location: inventory.php?success=1');
        exit;
    } else {
        $pdo->rollBack();
        $_SESSION['inventory_error'] = 'No se encontró el producto para actualizar.';
        header('Location: inventory.php');
        exit;
    }

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    $_SESSION['inventory_error'] = 'Error al actualizar inventario: ' . $e->getMessage();
    header('Location: inventory.php');
    exit;
}
