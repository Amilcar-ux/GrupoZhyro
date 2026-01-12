<?php
// UpdatePrecioEnvio.php
$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$idParam     = $_POST['id'] ?? '';
$precioParam = $_POST['precio_envio'] ?? '';

if ($idParam === '' || $precioParam === '') {
    header('Location: shipping.php?error=' . urlencode('Datos requeridos'));
    exit;
}

if (!ctype_digit($idParam)) {
    header('Location: shipping.php?error=' . urlencode('ID inválido'));
    exit;
}

if (!is_numeric($precioParam)) {
    header('Location: shipping.php?error=' . urlencode('Precio inválido'));
    exit;
}

$id     = (int)$idParam;
$precio = (float)$precioParam;

if ($precio < 0) {
    header('Location: shipping.php?error=' . urlencode('Precio no puede ser negativo'));
    exit;
}

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql  = 'UPDATE precio_envio_provincia SET precio_envio = :precio WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':precio' => $precio,
        ':id'     => $id
    ]);

    if ($stmt->rowCount() > 0) {
        header('Location: shipping.php?success=' . urlencode('Precio actualizado correctamente'));
    } else {
        header('Location: shipping.php?error=' . urlencode('Precio no encontrado'));
    }
    exit;

} catch (Exception $e) {
    header('Location: shipping.php?error=' . urlencode('Error al actualizar precio'));
    exit;
}
