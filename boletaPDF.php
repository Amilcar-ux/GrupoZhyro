<?php
// boletaPDF.php
require_once __DIR__ . '/fpdf.php';

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$idStr = $_GET['id'] ?? $_GET['idPedido'] ?? '';
if ($idStr === '' || !ctype_digit($idStr)) {
    die('Falta ID de pedido válido');
}
$idPedido = (int)$idStr;

function safe($v) { return $v === null ? '' : $v; }
function moneyF($v) { return number_format((float)$v, 2, '.', ''); }

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cabecera pedido
    $sqlCab = "SELECT p.ID_pedido, p.fecha, p.total, p.estado, p.codigo_unico,
                      c.nombre, c.apellido, c.telefono, c.direccion, c.provincia,
                      pa.metodo_pago, pa.codigo_yape, pa.comprobante
               FROM pedido p
               JOIN cliente c ON p.ID_cliente = c.ID_cliente
               LEFT JOIN pago pa ON pa.ID_pedido = p.ID_pedido
               WHERE p.ID_pedido = ?";
    $st = $pdo->prepare($sqlCab);
    $st->execute([$idPedido]);
    $pedido = $st->fetch(PDO::FETCH_ASSOC);
    if (!$pedido) {
        die('Pedido no encontrado');
    }

    // Detalle productos
    $sqlDet = "SELECT pr.nombre, dp.cantidad, dp.precio_unitario, dp.color, dp.talla
               FROM detallepedido dp
               JOIN producto pr ON pr.ID_producto = dp.ID_producto
               WHERE dp.ID_pedido = ?";
    $st = $pdo->prepare($sqlDet);
    $st->execute([$idPedido]);
    $detalles = $st->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die('Error BD: ' . $e->getMessage());
}

// Calcular subtotal y costo envío
$subtotal = 0.0;
foreach ($detalles as $d) {
    $subtotal += (float)$d['precio_unitario'] * (int)$d['cantidad'];
}
$totalBD    = (float)$pedido['total'];
$costoEnvio = $totalBD - $subtotal;
if ($costoEnvio < 0) $costoEnvio = 0.0;

// ==== Clase PDF tipo ticket ====
class BoletaFPDF extends FPDF {
    function Header() {
        // Logo
        $logoPath = __DIR__ . '/images/logo.jpg';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 35, 5, 20);
            $this->Ln(18);
        } else {
            $this->Ln(5);
        }

        $this->SetFont('Arial','',9);
        $this->Cell(0,4,utf8_decode('ZHYRO'),0,1,'C');
        $this->Cell(0,4,utf8_decode('EMPRESA DE ROPA'),0,1,'C');
        $this->Cell(0,4,utf8_decode('------------------'),0,1,'C');
        $this->Cell(0,4,utf8_decode('LA VICTORIA'),0,1,'C');
        $this->Cell(0,4,utf8_decode('Tel: 923 932 945'),0,1,'C');
        $this->Ln(3);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','',7);
        $this->Cell(0,4,utf8_decode('Representación impresa de la BOLETA DE VENTA ELECTRÓNICA.'),0,1,'C');
    }
}

// Documento tipo ticket 58mm
$pdf = new BoletaFPDF('P','mm',array(58,210));
$pdf->SetMargins(4,5,4);
$pdf->AddPage();

// Título boleta
$pdf->SetFont('Arial','B',9);
$pdf->MultiCell(0,4,utf8_decode("BOLETA DE VENTA ELECTRÓNICA\nB002 - ".$pedido['ID_pedido']),0,'C');
$pdf->Ln(2);

// Datos cliente
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(0,4,utf8_decode('CLIENTE: '.safe($pedido['nombre'].' '.$pedido['apellido'])),0,'L');
$pdf->MultiCell(0,4,utf8_decode('DIRECCIÓN: '.safe($pedido['direccion'])),0,'L');
$pdf->MultiCell(0,4,utf8_decode('TELÉFONO: '.safe($pedido['telefono'])),0,'L');
$pdf->Ln(2);

// Detalle
$pdf->SetFont('Arial','B',8);
$pdf->Cell(0,4,utf8_decode('DETALLE'),0,1,'L');
$pdf->Ln(1);

foreach ($detalles as $d) {
    $cantidad = (int)$d['cantidad'];
    $precioU  = (float)$d['precio_unitario'];
    $importe  = $cantidad * $precioU;

    $pdf->SetFont('Arial','B',8);
    $pdf->MultiCell(0,4,utf8_decode($cantidad.' UND  '.safe($d['nombre'])),0,'L');

    $specs = [];
    if (!empty($d['color'])) $specs[] = 'Color: '.$d['color'];
    if (!empty($d['talla'])) $specs[] = 'Talla: '.$d['talla'];
    if (!empty($specs)) {
        $pdf->SetFont('Arial','',7);
        $pdf->MultiCell(0,3,utf8_decode('('.implode(' | ',$specs).')'),0,'L');
    }

    $pdf->SetFont('Arial','',8);
    $pdf->MultiCell(
        0,4,
        utf8_decode('P. Unit: S/ '.moneyF($precioU).'   Importe: S/ '.moneyF($importe)),
        0,'R'
    );
    $pdf->Ln(1);
}

$pdf->Ln(2);

// Totales
$pdf->SetFont('Arial','B',8);
$pdf->Cell(0,4,utf8_decode('SUBTOTAL (S/): '.moneyF($subtotal)),0,1,'R');
$pdf->Cell(0,4,utf8_decode('COSTO ENVÍO (S/): '.moneyF($costoEnvio)),0,1,'R');
$pdf->Cell(0,4,utf8_decode('TOTAL (S/): '.moneyF($totalBD)),0,1,'R');

$pdf->Output('boleta_'.$idPedido.'.pdf','I');
