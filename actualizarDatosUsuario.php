<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Datos desde el formulario de login.php
$idCliente = isset($_POST['IDcliente']) ? (int)$_POST['IDcliente'] : 0;
$nombre    = trim($_POST['nombre']   ?? '');
$apellido  = trim($_POST['apellido'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion']?? '');

if ($idCliente <= 0 || $nombre === '' || $apellido === '') {
    $_SESSION['login_error'] = 'Faltan datos obligatorios para actualizar.';
    header('Location: login.php');
    exit;
}

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1) Actualizar datos en la tabla cliente
    $sql = "UPDATE cliente
            SET nombre = ?, apellido = ?, telefono = ?, direccion = ?
            WHERE ID_cliente = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $apellido, $telefono, $direccion, $idCliente]);

    // 2) Volver a leer el registro actualizado
    $sqlSel = "SELECT ID_cliente, nombre, apellido, telefono, direccion
               FROM cliente
               WHERE ID_cliente = ?";
    $stmtSel = $pdo->prepare($sqlSel);
    $stmtSel->execute([$idCliente]);
    $row = $stmtSel->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Actualizar datos en sesión
        $_SESSION['cliente'] = [
            'idCliente' => (int)$row['ID_cliente'],
            'nombre'    => $row['nombre'],
            'apellido'  => $row['apellido'],
            'telefono'  => $row['telefono'],
            'direccion' => $row['direccion'],
        ];

        // También actualizar el nombre mostrado en el header
        if (isset($_SESSION['usuario'])) {
            $_SESSION['usuario']['nombre'] = $row['nombre'];
        }
    }

    // Mensaje opcional de éxito
    $_SESSION['login_error'] = null;
    header('Location: login.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['login_error'] = 'Error al actualizar datos: ' . $e->getMessage();
    header('Location: login.php');
    exit;
}
