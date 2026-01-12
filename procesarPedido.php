<?php
session_start();

// 1) Actualizar método de pago y costo de envío según lo que se eligió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pago'])) {
        $_SESSION['metodoPago'] = $_POST['pago'];          // yape o transferencia
    }
    if (isset($_POST['costoEnvio'])) {
        $_SESSION['costoEnvio'] = (float)$_POST['costoEnvio'];
    }
}

// 2) Cargar datos desde sesión
$carrito = $_SESSION['carrito'] ?? [];
if (!is_array($carrito)) $carrito = [];

$cliente = $_SESSION['cliente'] ?? null;

$subtotal = 0;
foreach ($carrito as $item) {
    $precio   = isset($item['precio'])   ? (float)$item['precio']   : 0.0;
    $cantidad = isset($item['cantidad']) ? (int)$item['cantidad']   : 1;
    $subtotal += $precio * $cantidad;
}
$impuestos  = 0.0;
$costoEnvio = isset($_SESSION['costoEnvio']) ? (float)$_SESSION['costoEnvio'] : 0.0;
$total      = $subtotal + $impuestos + $costoEnvio;

// Si por alguna razón no se guardó, pon por defecto yape
$metodoPago  = $_SESSION['metodoPago']  ?? 'yape';

function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta charset="UTF-8"/>
  <title>Pago de Pedido</title>
  <link rel="stylesheet" href="css/procesarPedido.css"/>
  <link rel="stylesheet" href="css/index.css"/>
</head>
<body>

<div class="pasos">
  <span class="paso completado">Revisar pedido</span>
  <span class="paso completado">Dirección</span>
  <span class="paso activo">Pago</span>
</div>

<div class="direccion-facturacion">
  <strong>Facturación &amp; Envío:</strong>
  <?php
    if ($cliente) {
        echo esc(($cliente['direccion'] ?? '') . ', ' . ($cliente['provincia'] ?? ''));
    }
  ?>
</div>

<div class="contenedor-dos-columnas">

  <div class="formulario-direccion columna-formulario">
    <h3>Confirmación de Pago</h3>

    <?php if ($metodoPago === 'yape'): ?>
      <!-- Bloque YAPE -->
      <div class="qr-container">
        <img src="images/yape-qr.png" alt="Código QR Yape" />
        <p class="qr-numero">Número Yape: <strong>923932945</strong></p>
      </div>
      <p>Monto a transferir: <strong>S/ <?php echo number_format($total, 2); ?></strong></p>

      <form action="guardarPedido.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="metodoPago" value="yape"/>
        <label>Número de operación de Yape:
          <input type="text" name="codigoYape" required/>
        </label>
        <label>Subir comprobante (opcional):
          <input type="file" name="comprobante" accept="image/*"/>
        </label>
        <button type="submit" class="btn-continuar">Confirmar pago y registrar pedido</button>
      </form>

    <?php elseif ($metodoPago === 'transferencia'): ?>
      <!-- Bloque TRANSFERENCIA -->
      <div>
        <p>Realiza una transferencia bancaria a:</p>
        <ul>
          <li><strong>Titular: Amilcar Quispe</strong></li>
          <li><strong>BCP:</strong> 19193005423070</li>
          <li><strong>Interbank:</strong> 8983492368921</li>
          <li><strong>CCI Interbank:</strong> 00389801349236892147</li>
        </ul>
        <p>Monto a transferir: <strong>S/ <?php echo number_format($total, 2); ?></strong></p>
      </div>

      <form action="guardarPedido.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="metodoPago" value="transferencia"/>
        <label>Subir comprobante de transferencia:
          <input type="file" name="comprobante" accept="image/*" required/>
        </label>
        <button type="submit" class="btn-continuar">Confirmar pago y registrar pedido</button>
      </form>
    <?php endif; ?>
  </div>

  <aside class="resumen-orden columna-resumen">
    <h2>Resumen del pedido</h2>
    <?php foreach ($carrito as $item): ?>
      <div>
        <?php echo esc($item['nombre'] ?? ''); ?>
        x <?php echo (int)($item['cantidad'] ?? 1); ?> -
        S/ <?php echo number_format((float)($item['precio'] ?? 0), 2); ?>
      </div>
    <?php endforeach; ?>
    <div><strong>Subtotal:</strong> S/ <?php echo number_format($subtotal, 2); ?></div>
    <div><strong>Impuestos (0%):</strong> S/ <?php echo number_format($impuestos, 2); ?></div>
    <div><strong>Costo de envío:</strong> S/ <?php echo number_format($costoEnvio, 2); ?></div>
    <div><strong>Total:</strong> S/ <?php echo number_format($total, 2); ?></div>
  </aside>
</div>

</body>
</html>
