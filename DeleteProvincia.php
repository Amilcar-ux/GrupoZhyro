<?php
// DeleteProvincia.php
$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$idParam = $_POST['id_provincia'] ?? '';

if ($idParam === '') {
    header('Location: shipping.php?error=' . urlencode('ID de provincia requerido'));
    exit;
}

if (!ctype_digit($idParam)) {
    header('Location: shipping.php?error=' . urlencode('ID de provincia inválido'));
    exit;
}

$id = (int)$idParam;

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->beginTransaction();

    // 1. Eliminar precios de envío relacionados
    $stmt = $pdo->prepare('DELETE FROM precio_envio_provincia WHERE id_provincia = :id');
    $stmt->execute([':id' => $id]);

    // 2. Eliminar distritos relacionados
    $stmt = $pdo->prepare('DELETE FROM distrito WHERE id_provincia = :id');
    $stmt->execute([':id' => $id]);

    // 3. Eliminar provincia
    $stmt = $pdo->prepare('DELETE FROM provincia WHERE id_provincia = :id');
    $stmt->execute([':id' => $id]);
    $rows = $stmt->rowCount();

    if ($rows > 0) {
        $pdo->commit();
        header('Location: shipping.php?success=' . urlencode('Provincia y datos relacionados eliminados correctamente'));
    } else {
        $pdo->rollBack();
        header('Location: shipping.php?error=' . urlencode('Provincia no encontrada'));
    }
    exit;

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
        header('Location: shipping.php?error=' . urlencode('No se puede eliminar: provincia tiene datos relacionados'));
    } else {
        header('Location: shipping.php?error=' . urlencode('Error de base de datos: ' . $e->getMessage()));
    }
    exit;
} catch (Exception $e) {
    header('Location: shipping.php?error=' . urlencode('Error inesperado al eliminar provincia'));
    exit;
}
