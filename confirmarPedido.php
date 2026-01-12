<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$carrito = $_SESSION['carrito'] ?? [];
if (!is_array($carrito)) $carrito = [];

$cliente = $_SESSION['cliente'] ?? null;
$direccionCompleta = '';

if ($cliente) {
    $direccionCompleta = ($cliente['direccion'] ?? '') .
        ', ' . ($cliente['provincia'] ?? '') .
        ((isset($cliente['referencias']) && $cliente['referencias'] !== '')
            ? ' (Ref: ' . $cliente['referencias'] . ')'
            : '');
}

$subtotal = 0;
foreach ($carrito as $item) {
    $precio   = isset($item['precio'])   ? (float)$item['precio']   : 0.0;
    $cantidad = isset($item['cantidad']) ? (int)$item['cantidad']   : 1;
    $subtotal += $precio * $cantidad;
}
$impuestos = 0.0;

// vienen desde guardarDireccion.php
$provinciaSeleccionada = $provinciaSeleccionada   ?? 'Lima';
$precioEnvioProvincia  = $precioEnvioProvinciaVar ?? 0.00;

// guardar costo de envío en sesión para los siguientes pasos
$_SESSION['costoEnvio'] = $precioEnvioProvincia;

function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta charset="UTF-8" />
  <title>Confirmar Pedido</title>
  <link rel="stylesheet" href="css/confirmarPedido.css" />
  <link rel="stylesheet" href="css/index.css" />
</head>
<body>

<div class="pasos">
  <span class="paso completado">Revisar pedido</span>
  <span class="paso completado">Dirección</span>
  <span class="paso activo">Confirmar pedido</span>
</div>

<h2>Elegir tipo de entrega y método de pago</h2>

<div class="direccion-facturacion">
  <strong>Facturación &amp; Envío:</strong>
  <?php echo esc($direccionCompleta); ?>
  <a href="cargarProvincias.php">Editar</a>
</div>

<div class="contenedor-dos-columnas">
  <!-- IMPORTANTE: método POST, acción a procesarPedido.php -->
  <form class="formulario-direccion" id="formConfirmar" action="procesarPedido.php" method="POST">

    <div class="metodo-entrega">
      <?php if (strcasecmp($provinciaSeleccionada, 'Lima') === 0): ?>
        <label>
          <input type="radio" name="entrega" value="delivery-lima" checked />
          Delivery Lima - S/ <?php echo number_format($precioEnvioProvincia, 2); ?> (entrega en 1-2 días hábiles)
        </label>
      <?php else: ?>
        <label>
          <input type="radio" name="entrega" value="provincia" checked />
          Envío a <?php echo esc($provinciaSeleccionada); ?> - S/
          <?php echo number_format($precioEnvioProvincia, 2); ?>
        </label>
      <?php endif; ?>

      <!-- enviamos el costo de envío al siguiente paso -->
      <input type="hidden" name="costoEnvio"
             value="<?php echo number_format($precioEnvioProvincia, 2, '.', ''); ?>" />
    </div>

    <h3>Forma de pago</h3>
    <label class="metodo-pago">
      <input type="radio" name="pago" value="yape" checked />
      Aplicación Yape <img src="images/yape-logo.png" alt="Yape"/>
    </label>
    <label class="metodo-pago">
      <input type="radio" name="pago" value="transferencia" />
      Transferencia bancaria
      <img src="images/bcp-logo.png" alt="BCP"/>
      <img src="images/interbank-logo.png" alt="Interbank"/>
    </label>

    <div class="btn-container">
      <button type="submit" class="btn-continuar">Continuar</button>
      <button type="button" onclick="location.href='carrito.php'" class="btn-back">Volver al carrito</button>
    </div>
  </form>

  <aside class="resumen-orden">
    <h2>Resumen del pedido</h2>
    <?php foreach ($carrito as $item): ?>
      <div>
        <?php echo esc($item['nombre'] ?? ''); ?>
        x <?php echo (int)($item['cantidad'] ?? 1); ?> -
        S/ <?php echo number_format((float)($item['precio'] ?? 0), 2); ?>
      </div>
    <?php endforeach; ?>
    <div><strong>Subtotal:</strong> S/ <?php echo number_format($subtotal, 2); ?></div>
    <div><strong>Impuestos:</strong> S/ <?php echo number_format($impuestos, 2); ?></div>
    <div>
      <strong>Costo de envío:</strong>
      S/ <?php echo number_format($precioEnvioProvincia, 2); ?>
    </div>
    <div>
      <strong>Total:</strong>
      S/ <?php echo number_format($subtotal + $impuestos + $precioEnvioProvincia, 2); ?>
    </div>
  </aside>
</div>

</body>
</html>
