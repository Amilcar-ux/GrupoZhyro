<?php
// DeleteAdmin.php
session_start();

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$idAdmin = $_GET['ID_admin'] ?? '';

if ($idAdmin === '' || !ctype_digit($idAdmin)) {
    header('Location: admin.php');
    exit;
}

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // Soft delete: marcar usuario como Inactivo
    $sqlUser = 'UPDATE usuario u
                JOIN administrador a ON u.ID_usuario = a.ID_usuario
                SET u.estado = "Inactivo"
                WHERE a.ID_admin = :id';
    $stmt = $pdo->prepare($sqlUser);
    $stmt->execute([':id' => (int)$idAdmin]);

    $pdo->commit();
    header('Location: admin.php');
    exit;

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    $_SESSION['admin_error'] = 'Error al eliminar administrador: ' . $e->getMessage();
    header('Location: admin.php');
    exit;
}
