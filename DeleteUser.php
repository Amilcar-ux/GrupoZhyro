<?php
// DeleteUser.php
session_start();

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$idCliente = $_GET['ID_cliente'] ?? '';

if ($idCliente === '' || !ctype_digit($idCliente)) {
    header('Location: client.php');
    exit;
}

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // Soft delete: marcar usuario como Inactivo
    $sqlUser = 'UPDATE usuario u
                JOIN cliente c ON u.ID_usuario = c.ID_usuario
                SET u.estado = "Inactivo"
                WHERE c.ID_cliente = :id';
    $stmt = $pdo->prepare($sqlUser);
    $stmt->execute([':id' => (int)$idCliente]);

    $pdo->commit();
    header('Location: client.php');
    exit;

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    $_SESSION['client_error'] = 'Error al eliminar usuario: ' . $e->getMessage();
    header('Location: client.php');
    exit;
}
