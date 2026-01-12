<?php
// AddProvincia.php
$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$nombre = $_POST['nombre'] ?? '';

if ($nombre === null || trim($nombre) === '') {
    header('Location: shipping.php?error=' . urlencode('Nombre de provincia requerido'));
    exit;
}

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = 'INSERT INTO provincia (nombre) VALUES (:nombre)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':nombre' => trim($nombre)]);

    header('Location: shipping.php?success=' . urlencode('Provincia agregada correctamente'));
    exit;
} catch (Exception $e) {
    header('Location: shipping.php?error=' . urlencode('Error al agregar provincia: ' . $e->getMessage()));
    exit;
}
