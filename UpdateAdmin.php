<?php
// UpdateAdmin.php
session_start();

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$idAdmin  = $_POST['ID_admin'] ?? '';
$nombre   = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$rol      = $_POST['rol'] ?? '';
$estado   = $_POST['estado'] ?? '';

if ($idAdmin === '' || !ctype_digit($idAdmin)) {
    header('Location: admin.php');
    exit;
}

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // Actualizar tabla administrador
    $sqlAdmin = 'UPDATE administrador 
                 SET nombre = :nombre,
                     apellido = :apellido,
                     telefono = :telefono,
                     rol = :rol
                 WHERE ID_admin = :id';
    $stmt = $pdo->prepare($sqlAdmin);
    $stmt->execute([
        ':nombre'   => $nombre,
        ':apellido' => $apellido,
        ':telefono' => $telefono,
        ':rol'      => $rol,
        ':id'       => (int)$idAdmin
    ]);

    // Actualizar estado en usuario relacionado
    $sqlUser = 'UPDATE usuario u
                JOIN administrador a ON u.ID_usuario = a.ID_usuario
                SET u.estado = :estado
                WHERE a.ID_admin = :id';
    $stmt = $pdo->prepare($sqlUser);
    $stmt->execute([
        ':estado' => $estado,
        ':id'     => (int)$idAdmin
    ]);

    $pdo->commit();
    header('Location: admin.php');
    exit;

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    // Podrías guardar el error en sesión si quieres mostrarlo
    $_SESSION['admin_error'] = 'Error al actualizar administrador: ' . $e->getMessage();
    header('Location: admin.php');
    exit;
}
