<?php
session_start();

$carrito = $_SESSION['carrito'] ?? [];
if (!is_array($carrito)) $carrito = [];

$subtotal = 0;
foreach ($carrito as $item) {
    $precio   = isset($item['precio'])   ? (float)$item['precio']   : 0;
    $cantidad = isset($item['cantidad']) ? (int)$item['cantidad']   : 1;
    $subtotal += $precio * $cantidad;
}
$impuestos = 0;
$total     = $subtotal + $impuestos;

$usuarioObj    = $_SESSION['usuario'] ?? null;
$nombreUsuario = $usuarioObj['nombre'] ?? null;

$carritoVacio  = empty($carrito);
$colorResumen  = '';
$tallaResumen  = '';
$nombreResumen = '';

if (!$carritoVacio) {
    $primerItem   = $carrito[0];
    $colorResumen = $primerItem['color']  ?? '';
    $tallaResumen = $primerItem['talla']  ?? '';
    $nombreResumen= $primerItem['nombre'] ?? '';
}

if (!function_exists('esc')) {
    function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta charset="UTF-8" />
  <title>Carrito de Compras</title>
  <link rel="stylesheet" href="css/index.css" />
  <link rel="stylesheet" href="css/carrito.css" />
</head>
<body>

<header>
    <div class="logo">
        <a href="index.php">
            <img src="images/logo.png" alt="Logo Tienda">
        </a>
    </div>

    <nav>
        <ul>
            <li><a href="index.php">HOME</a></li>
            <li><a href="hombre.php">HOMBRE</a></li>
            <li><a href="bestsellers.php">BEST SELLERS</a></li>
            <li><a href="colecciones.php">COLECCIONES</a></li>
            <li><a href="promos.php">PROMOS</a></li>
        </ul>
    </nav>

    <div class="icons">
        <div class="user-area">
            <?php if ($nombreUsuario !== null): ?>
                <span class="user-name">
                    Hola, <strong><?php echo esc($nombreUsuario); ?></strong>
                </span>
                <form action="logout.php" method="post">
                    <button type="submit" class="btn-logout">Cerrar sesión</button>
                </form>
            <?php else: ?>
                <a href="login.php" class="user-name">Iniciar sesión</a>
            <?php endif; ?>

            <a href="miCuenta.php">
                <img src="images/user.png" alt="Cuenta">
            </a>
        </div>

        <a href="favoritos.php"><img src="images/heart.png" alt="Favoritos"></a>
        <a href="carrito.php"><img src="images/cart.png" alt="Carrito"></a>
    </div>
</header>

<h1>Carrito de Compras</h1>
<div class="container">
  <div class="productos">
    <?php if ($carritoVacio): ?>
      <p>No has agregado productos al carrito.</p>
      <a href="hombre.php" class="btn">Ver productos</a>
    <?php else: ?>
      <form id="formCarrito" action="modificarCarrito.php" method="post">
        <input type="hidden" name="accion" id="accion" value="">
        <input type="hidden" name="index" id="index" value="">
        <input type="hidden" name="operacion" id="operacion" value="">
        <?php foreach ($carrito as $i => $item): 
              $idProducto = (int)($item['idProducto'] ?? 0);
              $color      = $item['color']  ?? '';
              $talla      = $item['talla']  ?? '';
              $nombre     = $item['nombre'] ?? '';
              $precio     = isset($item['precio']) ? (float)$item['precio'] : 0;
              $imagen     = $item['imagen'] ?? '';
              $cantidad   = isset($item['cantidad']) ? (int)$item['cantidad'] : 1;
        ?>
          <div class="producto-item">
            <img src="images/<?php echo esc($imagen); ?>" alt="Producto <?php echo esc($nombre); ?>"/>
            <div class="info-producto">
              <p><strong><?php echo esc($nombre); ?></strong></p>
              <p>Precio: S/ <?php echo number_format($precio, 2); ?></p>
              <p>Color: <?php echo esc($color); ?></p>
              <p>Talla: <?php echo esc($talla); ?></p>
              <p>Artículo No: <?php echo $idProducto; ?></p>
              <div class="cantidad-control">
                <button type="button" class="btn" onclick="cambiarCantidad(<?php echo $i; ?>, 'disminuir')">-</button>
                <span>Cantidad: <?php echo $cantidad; ?></span>
                <button type="button" class="btn" onclick="cambiarCantidad(<?php echo $i; ?>, 'aumentar')">+</button>
                <button type="button" class="btn-eliminar" onclick="eliminarProducto(<?php echo $i; ?>)">Eliminar</button>
              </div>
              <div style="margin-top: 10px;">
                <a href="hombre.php" class="btn">Elegir Otro Producto</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </form>
    <?php endif; ?>
  </div>
  
  <div class="resumen">
    <h2>Resumen del pedido</h2>
    <?php if (!$carritoVacio): ?>
      <p><strong><?php echo esc($nombreResumen); ?></strong></p>
      <p>Color: <?php echo esc($colorResumen); ?></p>
      <p>Talla: <?php echo esc($tallaResumen); ?></p>
    <?php endif; ?>
    <p>Subtotal: S/ <?php echo number_format($subtotal, 2); ?></p>
    <p>Impuestos (0%): S/ <?php echo number_format($impuestos, 2); ?></p>
    <p><strong>Total: S/ <?php echo number_format($total, 2); ?></strong></p>

    <div class="metodos-pago">
      <h3>Métodos de pago</h3>
      <div style="margin-top: 10px;">
        <img src="images/yape-logo.png" alt="Yape" />
        <img src="images/bcp-logo.png" alt="BCP" />
        <img src="images/interbank-logo.png" alt="Interbank" />
      </div>
    </div>

    <?php if ($carritoVacio): ?>
      <button onclick="alert('El carrito está vacío. Agrega productos para continuar.'); return false;" class="proceder">Proceder al Pago</button>
    <?php elseif ($nombreUsuario === null): ?>
      <button onclick="window.location.href='login.php?redirect=carrito.php'; return false;" class="proceder">Proceder al Pago</button>
    <?php else: ?>
      <!-- pendiente: página de envío, por ahora solo ejemplo -->
      <button onclick="window.location.href='cargarProvincias.php';" class="proceder">Proceder al Pago</button>
    <?php endif; ?>
  </div>
</div>

<script>
  function cambiarCantidad(index, operacion) {
    const form = document.getElementById('formCarrito');
    document.getElementById('accion').value = 'cambiarCantidad';
    document.getElementById('index').value = index;
    document.getElementById('operacion').value = operacion;
    form.submit();
  }

  function eliminarProducto(index) {
    const form = document.getElementById('formCarrito');
    document.getElementById('accion').value = 'eliminar';
    document.getElementById('index').value = index;
    form.submit();
  }
</script>
<script src="js/app.js" defer></script>
<?php include 'foter.php'; ?>
</body>
</html>
