<?php
// AddProduct.php
session_start();

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$nombre        = $_POST['nombre'] ?? '';
$descripcion   = $_POST['descripcion'] ?? '';
$precioStr     = $_POST['precio'] ?? '0';
$stockStr      = $_POST['stock'] ?? '0';
$guiaCuidados  = $_POST['guia_cuidados'] ?? '';

$estado        = $_POST['estado'] ?? 'Activo';
$coleccion     = $_POST['coleccion'] ?? '';
$promo         = $_POST['promo'] ?? '';
$bestSellerStr = $_POST['bestSeller'] ?? '0';
$bestSeller    = ($bestSellerStr === '1') ? 1 : 0;

$colores    = $_POST['colores']    ?? [];
$tallas     = $_POST['tallas']     ?? [];
$materiales = $_POST['materiales'] ?? [];

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // Imagen principal
    $imagenPrincipal = null;
    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['imagen']['name']);
        $uploadDir = __DIR__ . '/images';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $destPath = $uploadDir . '/' . $fileName;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $destPath);
        $imagenPrincipal = $fileName; // en BD solo el nombre
    }

    // Insertar producto
    $sqlProd = 'INSERT INTO producto
            (nombre, descripcion, precio, stock, imagen, estado, coleccion, promo, best_seller)
            VALUES (:nombre, :descripcion, :precio, :stock, :imagen, :estado, :coleccion, :promo, :best)';
    $stmt = $pdo->prepare($sqlProd);
    $stmt->execute([
        ':nombre'      => $nombre,
        ':descripcion' => $descripcion,
        ':precio'      => (float)$precioStr,
        ':stock'       => (int)$stockStr,
        ':imagen'      => $imagenPrincipal,
        ':estado'      => $estado ?: 'Activo',
        ':coleccion'   => $coleccion !== '' ? $coleccion : null,
        ':promo'       => $promo !== '' ? $promo : null,
        ':best'        => $bestSeller
    ]);

    $idProducto = (int)$pdo->lastInsertId();

    // Colores y sus imágenes
    foreach ($colores as $idColorStr) {
        $idColor = (int)$idColorStr;

        // relación producto-color
        $stmt = $pdo->prepare('INSERT INTO producto_color (ID_producto, ID_color) VALUES (:p, :c)');
        $stmt->execute([':p' => $idProducto, ':c' => $idColor]);

        $field = 'imagen_color_' . $idColor;
        if (!empty($_FILES[$field]['name']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES[$field]['name']);
            $uploadDir = __DIR__ . '/images/colores';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $destPath = $uploadDir . '/' . $fileName;
            move_uploaded_file($_FILES[$field]['tmp_name'], $destPath);
            $relativePath = 'images/colores/' . $fileName;

            $stmt = $pdo->prepare(
                'INSERT INTO producto_color_imagenes (ID_producto, ID_color, imagen_url)
                 VALUES (:p, :c, :url)'
            );
            $stmt->execute([
                ':p'   => $idProducto,
                ':c'   => $idColor,
                ':url' => $relativePath
            ]);
        }
    }

    // Tallas
    if (!empty($tallas)) {
        $stmt = $pdo->prepare(
            'INSERT INTO producto_talla (ID_producto, ID_talla) VALUES (:p, :t)'
        );
        foreach ($tallas as $idTallaStr) {
            $stmt->execute([
                ':p' => $idProducto,
                ':t' => (int)$idTallaStr
            ]);
        }
    }

    // Materiales
    if (!empty($materiales)) {
        $stmt = $pdo->prepare(
            'INSERT INTO producto_material (ID_producto, ID_material) VALUES (:p, :m)'
        );
        foreach ($materiales as $idMatStr) {
            $stmt->execute([
                ':p' => $idProducto,
                ':m' => (int)$idMatStr
            ]);
        }
    }

    // Guía de cuidados
    if ($guiaCuidados !== '') {
        $stmt = $pdo->prepare(
            'INSERT INTO guia_cuidados (ID_producto, instrucciones) VALUES (:p, :g)'
        );
        $stmt->execute([
            ':p' => $idProducto,
            ':g' => $guiaCuidados
        ]);
    }

    $pdo->commit();
    header('Location: products.php');
    exit;

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    $_SESSION['products_error'] = 'Error al agregar producto: ' . $e->getMessage();
    header('Location: products.php');
    exit;
}
