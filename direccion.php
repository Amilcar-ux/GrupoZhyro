<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $provincias y $cliente vienen desde cargarProvincias.php
$provincias = $provincias ?? [];
$cliente    = $cliente    ?? null;

$carrito = $_SESSION['carrito'] ?? [];
if (!is_array($carrito)) $carrito = [];

$subtotal = 0;
foreach ($carrito as $item) {
    $precio   = isset($item['precio'])   ? (float)$item['precio']   : 0.0;
    $cantidad = isset($item['cantidad']) ? (int)$item['cantidad']   : 1;
    $subtotal += $precio * $cantidad;
}
$impuestos = 0;
$total     = $subtotal + $impuestos;

if (!function_exists('esc')) {
    function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta charset="UTF-8"/>
  <title>Dirección</title>
  <link rel="stylesheet" href="css/direccion.css"/>
  <link rel="stylesheet" href="css/index.css"/>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/direccion.js" defer></script>
</head>
<body>

<div class="pasos">
  <span class="paso completado">Revisar pedido</span>
  <span class="paso activo">Dirección</span>
  <span class="paso">Confirmar pedido</span>
</div>

<h2>Completa tu dirección o <a href="login.php">Identificarse</a></h2>

<div class="contenedor-direccion">
    <div class="formulario-direccion">
      <!-- GuardarDireccionServlet -> guardarDireccion.php -->
      <form action="guardarDireccion.php" method="POST" class="direccion-form">
        <label>Nombre Persona</label>
        <input type="text" name="nombrePersona" required
               value="<?php echo esc($cliente['nombre']    ?? ''); ?>" />

        <label>Apellido</label>
        <input type="text" name="apellidoPersona" required
               value="<?php echo esc($cliente['apellido']  ?? ''); ?>" />

        <label>Email</label>
        <input type="email" name="email" required
               value="<?php echo esc($cliente['email']     ?? ''); ?>" />

        <label>Teléfono</label>
        <input type="tel" name="telefono" placeholder="+51" required
               value="<?php echo esc($cliente['telefono']  ?? ''); ?>" />

        <label>Dirección y número</label>
        <input type="text" name="direccionNumero" required
               value="<?php echo esc($cliente['direccion'] ?? ''); ?>" />

        <label>Referencia</label>
        <input type="text" name="referencia"
               value="<?php echo esc($cliente['referencias'] ?? ''); ?>" />

        <label>Provincia / Departamento</label>
        <select name="provincia" id="provincia" required>
          <option value="" disabled selected>Seleccione una provincia</option>
          <?php foreach ($provincias as $p): ?>
            <?php
              $idProv   = (int)$p['id_provincia'];
              $nombreP  = $p['nombre'];
              $selected = ($cliente && isset($cliente['provincia']) && $cliente['provincia'] === $nombreP)
                          ? 'selected' : '';
            ?>
            <option value="<?php echo $idProv; ?>" <?php echo $selected; ?>>
              <?php echo esc($nombreP); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <script>
          document.getElementById('provincia').addEventListener('change', function() {
            var idProvincia = this.value;
            fetch('precioEnvio.php', {
              method: 'POST',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: 'provincia=' + encodeURIComponent(idProvincia)
            })
            .then(response => response.json())
            .then(data => {
              sessionStorage.setItem('precioEnvioProvincia', data.precioEnvio);
              console.log('Costo envío actualizado: S/ ' + data.precioEnvio);
            })
            .catch(err => console.error('Error al obtener precio envío:', err));
          });
        </script>

        <label>Distrito</label>
        <select name="distrito" id="distrito" required>
          <option value="">Seleccione un distrito</option>
          <!-- Se llenará con js/direccion.js -->
        </select>

        <label>
          <input type="checkbox" name="mismaDireccion" checked/> Enviar a la misma dirección
        </label>

        <button type="submit" class="btn-continuar">Siguiente</button>
        <button type="button" onclick="history.back()" class="btn-back">Atrás</button>
      </form>
    </div>

    <aside class="resumen-orden">
      <h2>Resumen del pedido</h2>
      <?php foreach ($carrito as $item): ?>
        <div>
          <?php echo esc($item['nombre'] ?? ''); ?>
          x <?php echo (int)($item['cantidad'] ?? 1); ?>
          - S/ <?php echo number_format((float)($item['precio'] ?? 0), 2); ?>
        </div>
      <?php endforeach; ?>
      <div><strong>Subtotal:</strong> S/ <?php echo number_format($subtotal, 2); ?></div>
      <div><strong>Impuestos (0%):</strong> S/ <?php echo number_format($impuestos, 2); ?></div>
      <div><strong>Total:</strong> S/ <?php echo number_format($total, 2); ?></div>
    </aside>
</div>

</body>
</html>
