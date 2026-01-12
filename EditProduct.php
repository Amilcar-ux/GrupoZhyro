<?php
// EditProduct.php
session_start();

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$idProducto = (int)($_POST['ID_producto'] ?? 0);
$nombre     = $_POST['nombre'] ?? '';
$descripcion= $_POST['descripcion'] ?? '';
$precioStr  = $_POST['precio'] ?? '0';
$stockStr   = $_POST['stock'] ?? '0';

$estado        = $_POST['estado'] ?? 'Activo';
$coleccion     = $_POST['coleccion'] ?? '';
$promo         = $_POST['promo'] ?? '';
$bestSellerStr = $_POST['bestSeller'] ?? '0';
$bestSeller    = ($bestSellerStr === '1') ? 1 : 0;

if ($idProducto <= 0) {
    header('Location: products.php');
    exit;
}

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // Actualizar datos básicos
    $sqlUpdate = 'UPDATE producto
                  SET nombre = :nombre,
                      descripcion = :descripcion,
                      precio = :precio,
                      stock = :stock,
                      estado = :estado,
                      coleccion = :coleccion,
                      promo = :promo,
                      best_seller = :best
                  WHERE ID_producto = :id';
    $stmt = $pdo->prepare($sqlUpdate);
    $stmt->execute([
        ':nombre'      => $nombre,
        ':descripcion' => $descripcion,
        ':precio'      => (float)$precioStr,
        ':stock'       => (int)$stockStr,
        ':estado'      => $estado ?: 'Activo',
        ':coleccion'   => $coleccion !== '' ? $coleccion : null,
        ':promo'       => $promo !== '' ? $promo : null,
        ':best'        => $bestSeller,
        ':id'          => $idProducto
    ]);

    // Actualizar imagen principal si viene archivo nuevo
    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $fileName  = basename($_FILES['imagen']['name']);
        $uploadDir = __DIR__ . '/images';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $destPath = $uploadDir . '/' . $fileName;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $destPath);

        $stmt = $pdo->prepare('UPDATE producto SET imagen = :img WHERE ID_producto = :id');
        $stmt->execute([
            ':img' => $fileName,
            ':id'  => $idProducto
        ]);
    }

    // Colores asociados al producto
    $stmt = $pdo->prepare('SELECT ID_color FROM producto_color WHERE ID_producto = :id');
    $stmt->execute([':id' => $idProducto]);
    $coloresProducto = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($coloresProducto as $idColor) {
        $idColor = (int)$idColor;
        $field   = 'imagen_color_' . $idColor;

        if (!empty($_FILES[$field]['name']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $fileName  = basename($_FILES[$field]['name']);
            $uploadDir = __DIR__ . '/images/colores';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $destPath = $uploadDir . '/' . $fileName;
            move_uploaded_file($_FILES[$field]['tmp_name'], $destPath);
            $relativePath = 'images/colores/' . $fileName;

            // Ver si ya existe registro en producto_color_imagenes
            $check = $pdo->prepare(
                'SELECT ID_imagen FROM producto_color_imagenes
                 WHERE ID_producto = :p AND ID_color = :c'
            );
            $check->execute([':p' => $idProducto, ':c' => $idColor]);
            $rowImg = $check->fetch(PDO::FETCH_ASSOC);

            if ($rowImg) {
                $stmt = $pdo->prepare(
                    'UPDATE producto_color_imagenes
                     SET imagen_url = :url
                     WHERE ID_imagen = :id'
                );
                $stmt->execute([
                    ':url' => $relativePath,
                    ':id'  => (int)$rowImg['ID_imagen']
                ]);
            } else {
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
    }

    $pdo->commit();
    header('Location: products.php');
    exit;

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    $_SESSION['products_error'] = 'Error al actualizar producto: ' . $e->getMessage();
    header('Location: products.php');
    exit;
}
