<?php
// UpdateUser.php
session_start();

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$idCliente = $_POST['ID_cliente'] ?? '';
$nombre    = $_POST['nombre'] ?? '';
$apellido  = $_POST['apellido'] ?? '';
$telefono  = $_POST['telefono'] ?? '';
$estado    = $_POST['estado'] ?? '';

if ($idCliente === '' || !ctype_digit($idCliente)) {
    header('Location: client.php');
    exit;
}

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // Actualizar cliente
    $sqlCli = 'UPDATE cliente 
               SET nombre = :nombre,
                   apellido = :apellido,
                   telefono = :telefono
               WHERE ID_cliente = :id';
    $stmt = $pdo->prepare($sqlCli);
    $stmt->execute([
        ':nombre'   => $nombre,
        ':apellido' => $apellido,
        ':telefono' => $telefono,
        ':id'       => (int)$idCliente
    ]);

    // Actualizar estado en usuario vinculado
    $sqlUser = 'UPDATE usuario u
                JOIN cliente c ON u.ID_usuario = c.ID_usuario
                SET u.estado = :estado
                WHERE c.ID_cliente = :id';
    $stmt = $pdo->prepare($sqlUser);
    $stmt->execute([
        ':estado' => $estado,
        ':id'     => (int)$idCliente
    ]);

    $pdo->commit();
    header('Location: client.php');
    exit;

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    $_SESSION['client_error'] = 'Error al actualizar usuario: ' . $e->getMessage();
    header('Location: client.php');
    exit;
}
