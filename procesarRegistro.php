<?php
session_start();

$email            = trim($_POST['email']            ?? '');
$password         = trim($_POST['password']         ?? '');
$confirmPassword  = trim($_POST['confirm_password'] ?? '');
$nombre           = trim($_POST['nombre']           ?? '');
$apellido         = trim($_POST['apellido']         ?? '');
$telefono         = trim($_POST['telefono']         ?? '');
$direccion        = trim($_POST['direccion']        ?? '');
$tipo             = $_POST['tipo'] ?? 'Cliente'; // por si acaso

// Validaciones básicas
if ($email === '' || $password === '' || $confirmPassword === '' ||
    $nombre === '' || $apellido === '') {

    $_SESSION['registro_error'] = 'Todos los campos obligatorios deben estar llenos.';
    header('Location: registro.php');
    exit;
}

if ($password !== $confirmPassword) {
    $_SESSION['registro_error'] = 'Las contraseñas no coinciden.';
    header('Location: registro.php');
    exit;
}

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Verificar si ya existe ese email
    $sqlCheck = "SELECT COUNT(*) FROM usuario WHERE email = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$email]);
    if ($stmtCheck->fetchColumn() > 0) {
        $_SESSION['registro_error'] = 'El correo ya está registrado.';
        header('Location: registro.php');
        exit;
    }

    // 2. Iniciar transacción (usuario + cliente)
    $pdo->beginTransaction();

    // 3. Insertar en tabla usuario (como en tu login: email + password SHA-256 + tipo)
    $hashedPassword = hash('sha256', $password);

    $sqlUser = "INSERT INTO usuario (email, password, tipo) VALUES (?, ?, ?)";
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([$email, $hashedPassword, $tipo]);

    $idUsuario = (int)$pdo->lastInsertId();

    // 4. Insertar en tabla cliente
    $sqlCliente = "INSERT INTO cliente (ID_usuario, nombre, apellido, telefono, direccion)
                   VALUES (?, ?, ?, ?, ?)";
    $stmtCli = $pdo->prepare($sqlCliente);
    $stmtCli->execute([$idUsuario, $nombre, $apellido, $telefono, $direccion]);

    $idCliente = (int)$pdo->lastInsertId();

    $pdo->commit();

    // 5. Crear sesión de usuario ya logueado
    $_SESSION['usuario'] = [
        'idUsuario' => $idUsuario,
        'nombre'    => $nombre,
        'email'     => $email,
    ];
    $_SESSION['cliente'] = [
        'idCliente' => $idCliente,
        'nombre'    => $nombre,
        'apellido'  => $apellido,
        'telefono'  => $telefono,
        'direccion' => $direccion,
    ];
    $_SESSION['pedidos'] = [];          // aún no tiene pedidos
    unset($_SESSION['registro_error']);

    // 6. Enviar al login/mi cuenta (puedes cambiar a index.php si prefieres)
    header('Location: miCuenta.php');
    exit;

} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['registro_error'] = 'Error al registrar usuario: ' . $e->getMessage();
    header('Location: registro.php');
    exit;
}
