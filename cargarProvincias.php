<?php
session_start();

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$user = 'root';
$pass = 'root';

$provincias = [];
$cliente    = $_SESSION['cliente'] ?? null;
$errorMsg   = null;

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT id_provincia, nombre FROM provincia";
    $stmt = $pdo->query($sql);
    $provincias = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $errorMsg = 'Error al cargar provincias: ' . $e->getMessage();
}

if (!function_exists('esc')) {
    function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
}
?>
<?php
// En lugar de forward, incluimos directamente la vista direccion.php
include 'direccion.php';
