<?php
session_start();

require __DIR__ . '/fpdf.php'; // ruta donde pusiste FPDF

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$dbUser = 'root';
$dbPass = 'root';

$idPedido = isset($_GET['idPedido']) ? (int)$_GET['idPedido'] : 0;
if ($idPedido <= 0) {
    die('Pedido inválido');
}

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1) Cabecera pedido + cliente
    $sqlP = "SELECT p.*, c.nombre, c.apellido, c.telefono, c.direccion, c.provincia
             FROM pedido p
             JOIN cliente c ON p.ID_cliente = c.ID_cliente
             WHERE p.ID_pedido = ?";
    $stmtP = $pdo->prepare($sqlP);
    $stmtP->execute([$idPedido]);
    $pedido = $stmtP->fetch(PDO::FETCH_ASSOC);
    if (!$pedido) {
        die('Pedido no encontrado');
    }

    // 2) Detalle con color y talla
    $sqlL = "SELECT dp.*, pr.nombre AS nombreProducto
             FROM detallepedido dp
             JOIN producto pr ON pr.ID_producto = dp.ID_producto
             WHERE dp.ID_pedido = ?";
    $stmtL = $pdo->prepare($sqlL);
    $stmtL->execute([$idPedido]);
    $lineas = $stmtL->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Error al cargar datos: ' . $e->getMessage());
}

// 3) cálculos
$opGravada = 0.0;
foreach ($lineas as $l) {
    $pu   = (float)$l['precio_unitario'];
    $cant = (int)$l['cantidad'];
    $opGravada += $pu * $cant;
}
$igv = 0.0;

$costoEnvio = 0.0;
if (isset($_SESSION['costoEnvio']) && is_numeric($_SESSION['costoEnvio'])) {
    $costoEnvio = (float)$_SESSION['costoEnvio'];
}
$total = $opGravada + $igv + $costoEnvio;

// 4) generar PDF con FPDF
class BoletaPDF extends FPDF {
    function header() {
        // logo
        if (file_exists(__DIR__ . '/images/logo.jpg')) {
            $this->Image(__DIR__ . '/images/logo.jpg', 10, 8, 25);
        }
        // título empresa
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(80);
        $this->Cell(30, 6, 'ZHYRO', 0, 1, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(80);
        $this->Cell(30, 5, 'LA VICTORIA / LIMA / PERU', 0, 1, 'L');
        $this->Cell(80);
        $this->Cell(30, 5, 'Tel: 923 932 945', 0, 1, 'L');
        $this->Ln(5);
    }
}

$pdf = new BoletaPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

// Boleta info
$pdf->SetXY(130, 15);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(70, 6, 'BOLETA DE VENTA ELECTRONICA', 1, 2, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(70, 6, 'N° B00' . (int)$pedido['ID_pedido'], 1, 1, 'C');
$pdf->Ln(10);

// Datos cliente
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(30, 6, 'Cliente:', 0, 0);
$pdf->Cell(80, 6, ($pedido['nombre'] ?? '') . ' ' . ($pedido['apellido'] ?? ''), 0, 1);
$pdf->Cell(30, 6, 'Direccion:', 0, 0);
$dir = $pedido['direccion'] ?? '';
$pdf->Cell(80, 6, $dir !== '' ? $dir : '-', 0, 1);
$pdf->Cell(30, 6, 'DNI:', 0, 0);
$pdf->Cell(80, 6, '-', 0, 1);
$pdf->Ln(5);

// Tabla info cabecera
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 6, 'FECHA EMISION', 1, 0, 'C');
$pdf->Cell(40, 6, 'FECHA VENCIMIENTO', 1, 0, 'C');
$pdf->Cell(40, 6, 'COND. DE PAGO', 1, 1, 'C');

$pdf->SetFont('Arial', '', 8);
$pdf->Cell(40, 6, $pedido['fecha'], 1, 0, 'C');
$pdf->Cell(40, 6, '-', 1, 0, 'C');
$pdf->Cell(40, 6, 'CONTADO', 1, 1, 'C');
$pdf->Ln(5);

// Detalle
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 6, 'CANT.', 1, 0, 'C');
$pdf->Cell(15, 6, 'U.M.', 1, 0, 'C');
$pdf->Cell(55, 6, 'DESCRIPCION', 1, 0, 'C');
$pdf->Cell(20, 6, 'COLOR', 1, 0, 'C');
$pdf->Cell(20, 6, 'TALLA', 1, 0, 'C');
$pdf->Cell(25, 6, 'PRECIO UNIT.', 1, 0, 'C');
$pdf->Cell(25, 6, 'IMPORTE', 1, 1, 'C');

$pdf->SetFont('Arial', '', 8);
foreach ($lineas as $l) {
    $cant  = (int)$l['cantidad'];
    $pu    = (float)$l['precio_unitario'];
    $imp   = $cant * $pu;
    $color = $l['color'] ?? '-';
    $talla = $l['talla'] ?? '-';

    $pdf->Cell(15, 6, $cant, 1, 0, 'C');
    $pdf->Cell(15, 6, 'UNIDAD', 1, 0, 'C');
    $pdf->Cell(55, 6, $l['nombreProducto'], 1, 0, 'L');
    $pdf->Cell(20, 6, $color !== '' ? $color : '-', 1, 0, 'C');
    $pdf->Cell(20, 6, $talla !== '' ? $talla : '-', 1, 0, 'C');
    $pdf->Cell(25, 6, 'S/ ' . number_format($pu, 2), 1, 0, 'R');
    $pdf->Cell(25, 6, 'S/ ' . number_format($imp, 2), 1, 1, 'R');
}
$pdf->Ln(5);

// Totales
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(40, 6, 'OP. GRAVADA (S/)', 1, 0);
$pdf->Cell(30, 6, number_format($opGravada, 2), 1, 1, 'R');
$pdf->Cell(40, 6, 'TOTAL IGV (S/)', 1, 0);
$pdf->Cell(30, 6, number_format($igv, 2), 1, 1, 'R');
$pdf->Cell(40, 6, 'COSTO DE ENVIO (S/)', 1, 0);
$pdf->Cell(30, 6, number_format($costoEnvio, 2), 1, 1, 'R');
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 6, 'IMPORTE TOTAL (S/)', 1, 0);
$pdf->Cell(30, 6, number_format($total, 2), 1, 1, 'R');

$pdf->Ln(8);
$pdf->SetFont('Arial',  '', 8);
$pdf->Cell(0, 5, 'Representacion impresa de la BOLETA DE VENTA ELECTRONICA.', 0, 1, 'L');
$pdf->Cell(0, 5, 'Gracias por su compra.', 0, 1, 'L');

// 5) salida como descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="boleta_'.$idPedido.'.pdf"');
$pdf->Output('I', 'boleta_'.$idPedido.'.pdf');
exit;
