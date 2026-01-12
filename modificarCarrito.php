<?php
session_start();

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
$carrito = $_SESSION['carrito'];

$accion = $_POST['accion'] ?? '';
$index  = isset($_POST['index']) ? (int)$_POST['index'] : -1;

if ($accion === 'cambiarCantidad' && $index >= 0 && $index < count($carrito)) {
    $operacion = $_POST['operacion'] ?? '';
    $item      = $carrito[$index];
    $cantidad  = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;

    if ($operacion === 'aumentar') {
        $cantidad++;
    } elseif ($operacion === 'disminuir' && $cantidad > 1) {
        $cantidad--;
    }

    $item['cantidad']   = $cantidad;
    $carrito[$index]    = $item;

} elseif ($accion === 'eliminar' && $index >= 0 && $index < count($carrito)) {
    array_splice($carrito, $index, 1);
}

$_SESSION['carrito'] = $carrito;

header('Location: carrito.php');
exit;
