<?php
session_start();

$idProducto = isset($_POST['idProducto']) ? (int)$_POST['idProducto'] : 0;
$color      = $_POST['color'] ?? '';
$talla      = $_POST['talla'] ?? '';

if ($idProducto <= 0) {
    header('Location: index.php');
    exit;
}

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$user = 'root';
$pass = 'root';

$nombre = '';
$precio = 0;
$imagen = '';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT nombre, precio, imagen FROM producto WHERE ID_producto = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idProducto]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nombre = $row['nombre'];
        $precio = (float)$row['precio'];
        $imagen = $row['imagen'];
    }
} catch (PDOException $e) {
    // podrías guardar error en sesión si quieres
}

// Obtener carrito actual de sesión
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
$carrito = $_SESSION['carrito'];

// Crear item
$item = [
    'idProducto' => $idProducto,
    'color'      => $color,
    'talla'      => $talla,
    'nombre'     => $nombre,
    'precio'     => $precio,
    'imagen'     => $imagen,
    'cantidad'   => 1,
];

$carrito[] = $item;
$_SESSION['carrito'] = $carrito;

header('Location: carrito.php');
exit;
