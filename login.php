<?php
session_start();

$usuario = $_SESSION['usuario'] ?? null;
$nombreUsuario = $usuario['nombre'] ?? null;

$cliente = $_SESSION['cliente'] ?? null;
$pedidos = $_SESSION['pedidos'] ?? [];

$errorMsg = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta charset="UTF-8" />
  <title>Iniciar Sesión</title>
  <link rel="stylesheet" href="css/login.css" />
  <link rel="stylesheet" href="css/index.css">
  <script src="js/app.js" defer></script>
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

<?php if ($usuario === null): ?>

    <!-- FORMULARIO LOGIN -->
    <div class="login-centrado">
      <div class="login-container">
        <img src="images/user.png" alt="Usuario" class="login-icon" />
        <h1>Iniciar Sesión</h1>

        <?php if (!empty($errorMsg)): ?>
          <div class="error-mensaje"><?php echo esc($errorMsg); ?></div>
        <?php endif; ?>

        <form action="procesarLogin.php" method="post">
          <input type="text" name="username" placeholder="Email" required />
            <div class="password-row">
                <input id="password" type="password" name="password" placeholder="Contraseña" required />
                <button type="button" class="toggle-password" onclick="togglePassword()">👁</button>
            </div>
          <input type="hidden" name="redirect" value="<?php echo esc($redirect); ?>" />
          <button type="submit">Entrar</button>
        </form>
        <p class="nota">¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
      </div>
    </div>

<?php else: ?>

    <!-- DASHBOARD USUARIO -->
    <div class="user-dashboard">
      <div class="purchase-history">
        <h2>Historial de Compras</h2>

        <?php if (empty($pedidos)): ?>
          <p>No tienes compras registradas.</p>
        <?php else: ?>
          <table>
            <thead>
              <tr><th>Pedido</th><th>Fecha</th><th>Estado</th><th>Total</th></tr>
            </thead>
            <tbody>
              <?php foreach ($pedidos as $pedido): ?>
                <tr>
                  <td><?php echo esc($pedido['IDpedido']); ?></td>
                  <td><?php echo esc($pedido['fecha']); ?></td>
                  <td><?php echo esc($pedido['estado']); ?></td>
                  <td><?php echo esc($pedido['total']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div class="edit-user-data">
        <h2>Modificar Datos</h2>
        <form action="actualizarDatosUsuario.php" method="post">
          <input type="hidden" name="IDcliente"
                 value="<?php echo esc($cliente['idCliente'] ?? ''); ?>" />
          <label>Nombre:
            <input type="text" name="nombre"
                   value="<?php echo esc($cliente['nombre'] ?? ''); ?>" required />
          </label>
          <label>Apellido:
            <input type="text" name="apellido"
                   value="<?php echo esc($cliente['apellido'] ?? ''); ?>" required />
          </label>
          <label>Teléfono:
            <input type="text" name="telefono"
                   value="<?php echo esc($cliente['telefono'] ?? ''); ?>" />
          </label>
          <label>Dirección:
            <input type="text" name="direccion"
                   value="<?php echo esc($cliente['direccion'] ?? ''); ?>" />
          </label>
          <button type="submit">Actualizar</button>
        </form>
      </div>
    </div>

<?php endif; ?>

<?php include 'foter.php'; ?>
    
    <script>
function togglePassword() {
    const input = document.getElementById('password');
    if (!input) return;

    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}
</script>

</body>
</html>
