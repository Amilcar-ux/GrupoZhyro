<?php
session_start();

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$dbUser = 'root';
$dbPass = 'root';

$idPedido = isset($_GET['idPedido']) ? (int)$_GET['idPedido'] : 0;
if ($idPedido <= 0) {
    die('Pedido inválido');
}

function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pedido
    $sqlP = "SELECT * FROM pedido WHERE ID_pedido = ?";
    $stmtP = $pdo->prepare($sqlP);
    $stmtP->execute([$idPedido]);
    $pedido = $stmtP->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        die('Pedido no encontrado');
    }

    // Cliente
    $sqlC = "SELECT c.* FROM cliente c
             JOIN pedido p ON p.ID_cliente = c.ID_cliente
             WHERE p.ID_pedido = ?";
    $stmtC = $pdo->prepare($sqlC);
    $stmtC->execute([$idPedido]);
    $cliente = $stmtC->fetch(PDO::FETCH_ASSOC);

    // Líneas de pedido
    $sqlL = "SELECT dp.*, pr.nombre AS nombreProducto
             FROM detallepedido dp
             JOIN producto pr ON pr.ID_producto = dp.ID_producto
             WHERE dp.ID_pedido = ?";
    $stmtL = $pdo->prepare($sqlL);
    $stmtL->execute([$idPedido]);
    $lineas = $stmtL->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Error al cargar boleta: ' . $e->getMessage());
}

// cálculos
$opGravada = 0.0;
foreach ($lineas as $l) {
    $pu  = (float)$l['precio_unitario'];
    $cant= (int)$l['cantidad'];
    $opGravada += $pu * $cant;
}
$igv = 0.0;

// costo envío desde sesión
$costoEnvio = 0.0;
if (isset($_SESSION['costoEnvio'])) {
    $ce = $_SESSION['costoEnvio'];
    $costoEnvio = is_numeric($ce) ? (float)$ce : 0.0;
}

$total = $opGravada + $igv + $costoEnvio;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Venta</title>
    <link rel="stylesheet" href="css/boleta.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<div class="boleta-wrapper">
    <!-- CABECERA EMPRESA -->
    <header class="boleta-header">
        <div class="empresa">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>
            <div class="empresa-datos">
                <h1>ZHYRO</h1>
                <p> LA VICTORIA / LIMA / PERÚ </p>
                <p>Tel: 923 932 945</p>
                <p>Email:  zhyrope@gmail.com</p>
                <p>Web: www.zhyro.com</p>
            </div>
        </div>
        <div class="boleta-info">
            <h2>BOLETA DE VENTA ELECTRÓNICA</h2>
            <p>N° B00<?php echo (int)$pedido['ID_pedido']; ?></p>
        </div>
    </header>

    <!-- DATOS CLIENTE -->
    <section class="cliente-datos">
        <p><strong>Cliente:</strong>
            <?php echo esc(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? '')); ?>
        </p>
        <p><strong>Dirección:</strong>
            <?php
            $dir = $cliente['direccion'] ?? '';
            echo $dir !== '' ? esc($dir) : '-';
            ?>
        </p>
        <p><strong>DNI:</strong> -</p>
    </section>

    <!-- FECHAS -->
    <section class="pedido-info">
        <table>
            <tr><th>FECHA EMISIÓN</th><th>FECHA VENCIMIENTO</th><th>COND. DE PAGO</th></tr>
            <tr>
                <td><?php echo esc($pedido['fecha']); ?></td>
                <td>-</td>
                <td>CONTADO</td>
            </tr>
        </table>
    </section>

    <!-- DETALLE -->
    <section class="detalle">
        <table>
            <thead>
            <tr>
                <th>CANT.</th>
                <th>U.M.</th>
                <th>DESCRIPCIÓN</th>
                <th>COLOR</th>
                <th>TALLA</th>
                <th>PRECIO UNIT.</th>
                <th>IMPORTE</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($lineas)): ?>
                <?php foreach ($lineas as $l):
                    $pu  = (float)$l['precio_unitario'];
                    $cant= (int)$l['cantidad'];
                    $imp = $pu * $cant;
                ?>
                <tr>
                    <td><?php echo $cant; ?></td>
                    <td>UNIDAD</td>
                    <td><?php echo esc($l['nombreProducto']); ?></td>
                    <td><?php echo $l['color'] !== null && $l['color'] !== '' ? esc($l['color']) : '-'; ?></td>
                    <td><?php echo $l['talla'] !== null && $l['talla'] !== '' ? esc($l['talla']) : '-'; ?></td>
                    <td><?php echo 'S/ ' . number_format($pu, 2); ?></td>
                    <td><?php echo 'S/ ' . number_format($imp, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">No hay detalle para este pedido.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- TOTALES -->
    <section class="totales">
        <table>
            <tr><td>OP. GRAVADA (S/)</td><td><?php echo number_format($opGravada, 2); ?></td></tr>
            <tr><td>TOTAL IGV (S/)</td><td><?php echo number_format($igv, 2); ?></td></tr>
            <tr><td>COSTO DE ENVÍO (S/)</td><td><?php echo number_format($costoEnvio, 2); ?></td></tr>
            <tr><th>IMPORTE TOTAL (S/)</th><th><?php echo number_format($total, 2); ?></th></tr>
        </table>
    </section>

    <!-- BOTONES -->
    <div class="acciones">
        <!-- PDF: puedes hacer otro script pdf_boleta.php -->
        <form action="descargar_boleta.php" method="get">
            <input type="hidden" name="idPedido" value="<?php echo (int)$pedido['ID_pedido']; ?>">
            <button type="submit">Descargar PDF</button>
        </form>
        <div style="margin-top: 20px; text-align: center;">
            <a href="index.php" class="btn-volver">Volver a la tienda</a>
        </div>
    </div>
</div>
</body>
</html>
