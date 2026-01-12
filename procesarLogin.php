<?php
session_start();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$redirect = $_POST['redirect'] ?? 'index.php';

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Debes ingresar email y contraseña.';
    header('Location: login.php');
    exit;
}

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Validar usuario (igual que tu servlet)
    $sqlCheckUser = "SELECT ID_usuario, password 
                     FROM usuario 
                     WHERE email = ?";
    $stmt = $pdo->prepare($sqlCheckUser);
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $_SESSION['login_error'] = 'Usuario no encontrado';
        header('Location: login.php');
        exit;
    }

    $idUsuario     = (int)$row['ID_usuario'];
    $hashedPassDB  = $row['password'];

    // hash SHA-256 de la contraseña ingresada (igual que en Java)
    $hashedInputPass = hash('sha256', $password);

    if (strcasecmp($hashedPassDB, $hashedInputPass) !== 0) {
        $_SESSION['login_error'] = 'Contraseña incorrecta';
        header('Location: login.php');
        exit;
    }

    // 2. Construir "Usuario" en sesión (datos básicos)
    // Primero intentamos sacar datos de cliente para obtener el nombre
    $sqlCliente = "SELECT ID_cliente, nombre, apellido, telefono, direccion
                   FROM cliente
                   WHERE ID_usuario = ?";
    $stmtCli = $pdo->prepare($sqlCliente);
    $stmtCli->execute([$idUsuario]);
    $rowCli = $stmtCli->fetch(PDO::FETCH_ASSOC);

    $cliente  = null;
    $idCliente = -1;
    $nombreUsuario = null;

    if ($rowCli) {
        $idCliente = (int)$rowCli['ID_cliente'];
        $cliente = [
            'idCliente' => $idCliente,
            'nombre'    => $rowCli['nombre'],
            'apellido'  => $rowCli['apellido'],
            'telefono'  => $rowCli['telefono'],
            'direccion' => $rowCli['direccion'],
        ];
        $nombreUsuario = $rowCli['nombre'];
    }

    // 3. Obtener pedidos del cliente (solo si existe cliente)
    $pedidos = [];
    if ($idCliente !== -1) {
        $sqlPed = "SELECT ID_pedido, ID_cliente, fecha, estado, total
                   FROM pedido
                   WHERE ID_cliente = ?
                   ORDER BY fecha DESC";
        $stmtPed = $pdo->prepare($sqlPed);
        $stmtPed->execute([$idCliente]);
        while ($rowPed = $stmtPed->fetch(PDO::FETCH_ASSOC)) {
            $pedidos[] = [
                'IDpedido' => $rowPed['ID_pedido'],
                'fecha'    => $rowPed['fecha'],
                'estado'   => $rowPed['estado'],
                'total'    => $rowPed['total'],
            ];
        }
    }

    // 4. Guardar todo en sesión (equivalente a session.setAttribute("usuario", usuarioObj))
    $_SESSION['usuario'] = [
        'idUsuario' => $idUsuario,
        'email'     => $username,
        'nombre'    => $nombreUsuario ?? $username, // por si no hay cliente
    ];
    $_SESSION['cliente'] = $cliente;
    $_SESSION['pedidos'] = $pedidos;

    unset($_SESSION['login_error']);

    // 5. Después de loguear, ir a miCuenta (como en tu servlet)
    header('Location: miCuenta.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['login_error'] = 'Error de base de datos: ' . $e->getMessage();
    header('Location: login.php');
    exit;
}
