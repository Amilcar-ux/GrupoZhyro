<?php
// AddPrecioEnvio.php
$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$idProvinciaParam = $_POST['id_provincia'] ?? '';
$precioParam      = $_POST['precio_envio'] ?? '';

if ($idProvinciaParam === '' || $precioParam === '') {
    header('Location: shipping.php?error=' . urlencode('Datos requeridos'));
    exit;
}

if (!ctype_digit($idProvinciaParam) || !is_numeric($precioParam)) {
    header('Location: shipping.php?error=' . urlencode('Precio o ID inválido'));
    exit;
}

$idProvincia = (int)$idProvinciaParam;
$precio      = (float)$precioParam;

if ($precio < 0) {
    header('Location: shipping.php?error=' . urlencode('Precio no puede ser negativo'));
    exit;
}

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si ya existe precio para esta provincia
    $check = $pdo->prepare('SELECT id FROM precio_envio_provincia WHERE id_provincia = :idp');
    $check->execute([':idp' => $idProvincia]);
    if ($check->fetch(PDO::FETCH_ASSOC)) {
        header('Location: shipping.php?error=' . urlencode('Ya existe precio para esta provincia'));
        exit;
    }

    // Insertar nuevo precio
    $stmt = $pdo->prepare(
        'INSERT INTO precio_envio_provincia (id_provincia, precio_envio) VALUES (:idp, :precio)'
    );
    $stmt->execute([
        ':idp'    => $idProvincia,
        ':precio' => $precio
    ]);

    header('Location: shipping.php?success=' . urlencode('Precio de envío agregado correctamente'));
    exit;

} catch (Exception $e) {
    header('Location: shipping.php?error=' . urlencode('Error al agregar precio de envío'));
    exit;
}
