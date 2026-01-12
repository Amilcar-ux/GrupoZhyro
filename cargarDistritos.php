<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$user = 'root';
$pass = 'root';

$provinciaId = isset($_GET['provinciaId']) ? (int)$_GET['provinciaId'] : 0;

if ($provinciaId <= 0) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT id_distrito, nombre FROM distrito WHERE id_provincia = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$provinciaId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Adaptar claves a lo que usa tu JS: id / nombre
    $distritos = [];
    foreach ($rows as $r) {
        $distritos[] = [
            'id'     => (int)$r['id_distrito'],
            'nombre' => $r['nombre'],
        ];
    }

    echo json_encode($distritos);
} catch (PDOException $e) {
    // En caso de error, devuelve array vacío para que dispare tu alert
    echo json_encode([]);
}
