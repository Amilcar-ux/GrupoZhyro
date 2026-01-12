<?php
// UpdateProvincia.php
$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$nombre = $_POST['nombre'] ?? '';
$idParam = $_POST['id_provincia'] ?? '';

if (trim($nombre) === '' || $idParam === '') {
    header('Location: shipping.php?error=' . urlencode('Datos incompletos'));
    exit;
}

if (!ctype_digit($idParam)) {
    header('Location: shipping.php?error=' . urlencode('ID de provincia inválido'));
    exit;
}

$idProvincia = (int)$idParam;

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = 'UPDATE provincia SET nombre = :nombre WHERE id_provincia = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => trim($nombre),
        ':id'     => $idProvincia
    ]);

    if ($stmt->rowCount() > 0) {
        header('Location: shipping.php?success=' . urlencode('Provincia actualizada correctamente'));
    } else {
        header('Location: shipping.php?error=' . urlencode('Provincia no encontrada'));
    }
    exit;

} catch (Exception $e) {
    header('Location: shipping.php?error=' . urlencode('Error al actualizar provincia'));
    exit;
}
