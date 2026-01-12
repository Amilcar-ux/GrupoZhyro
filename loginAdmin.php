<?php
session_start();

$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['errorMessage'] = 'Email y contraseña son obligatorios';
    header('Location: indexAdmin.php');
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT ID_usuario, email, password, tipo
            FROM usuario
            WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['tipo'] === 'Administrador' && hash_equals($user['password'], hash('sha256', $password))) {
        // tus contraseñas parecen SHA-256 plano, no password_hash
        $_SESSION['userType'] = 'Administrador';
        $_SESSION['userName'] = $user['email'];
        $_SESSION['userId']   = $user['ID_usuario'];
        header('Location: home.php');
        exit;
    } else {
        $_SESSION['errorMessage'] = 'Credenciales inválidas o no eres administrador';
        header('Location: indexAdmin.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['errorMessage'] = 'Error en el servidor';
    header('Location: indexAdmin.php');
    exit;
}
