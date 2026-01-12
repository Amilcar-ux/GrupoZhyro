<?php
// DeletePrecioEnvio.php
$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$idParam = $_POST['id'] ?? '';

if ($idParam === '') {
    header('Location: shipping.php?error=' . urlencode('ID requerido'));
    exit;
}

if (!ctype_digit($idParam)) {
    header('Location: shipping.php?error=' . urlencode('ID inválido'));
    exit;
}

$id = (int)$idParam;

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('DELETE FROM precio_envio_provincia WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $rows = $stmt->rowCount();

    if ($rows > 0) {
        header('Location: shipping.php?success=' . urlencode('Precio eliminado correctamente'));
    } else {
        header('Location: shipping.php?error=' . urlencode('Precio no encontrado'));
    }
    exit;

} catch (Exception $e) {
    header('Location: shipping.php?error=' . urlencode('Error al eliminar precio'));
    exit;
}
