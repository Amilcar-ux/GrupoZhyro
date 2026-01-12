<?php
session_start();

$usuario = $_SESSION['usuario'] ?? null;
$nombreUsuario = $usuario['nombre'] ?? null;
$errorMsg = $_SESSION['registro_error'] ?? null;
unset($_SESSION['registro_error']);

function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Nuevo Usuario</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/registro.css">
  <link rel="stylesheet" href="css/index.css">
</head>
<body>

<header>
    <!-- LOGO TIENDA -->
    <div class="logo">
        <a href="index.php">
            <img src="images/logo.png" alt="Logo Tienda">
        </a>
    </div>

    <!-- MENÚ -->
    <nav>
        <ul>
            <li><a href="index.php">HOME</a></li>
            <li><a href="hombre.php">HOMBRE</a></li>
            <li><a href="bestsellers.php">BEST SELLERS</a></li>
            <li><a href="colecciones.php">COLECCIONES</a></li>
            <li><a href="promos.php">PROMOS</a></li>
        </ul>
    </nav>

    <!-- ICONOS + USUARIO -->
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


<main class="registro-main">
  <div class="registro-card">
    <h1>Crear una cuenta</h1>

    <?php if (!empty($errorMsg)): ?>
      <div class="registro-error"><?php echo esc($errorMsg); ?></div>
    <?php endif; ?>

    <form action="procesarRegistro.php" method="post" id="formRegistro">
      <label for="email">Correo electrónico:</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Contraseña:</label>
      <div class="password-row">
        <input type="password" id="password" name="password" required>
        <button type="button" class="toggle-password" onclick="toggleRegistroPassword('password')">Mostrar</button>
      </div>

      <label for="confirm_password">Confirmar contraseña:</label>
      <div class="password-row">
        <input type="password" id="confirm_password" name="confirm_password" required>
        <button type="button" class="toggle-password" onclick="toggleRegistroPassword('confirm_password')">Mostrar</button>
      </div>

      <label for="nombre">Nombre:</label>
      <input type="text" id="nombre" name="nombre" required>

      <label for="apellido">Apellido:</label>
      <input type="text" id="apellido" name="apellido" required>

      <label for="telefono">Teléfono:</label>
      <input type="tel" id="telefono" name="telefono">

      <label for="direccion">Dirección:</label>
      <textarea id="direccion" name="direccion"></textarea>

      <input type="hidden" name="tipo" value="Cliente">

      <button type="submit" class="btn-registrarse">Registrarse</button>
    </form>

    <p class="registro-nota">
      ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
    </p>
  </div>
</main>

<?php include 'foter.php'; ?>

<script>
function toggleRegistroPassword(id) {
  const input = document.getElementById(id);
  if (!input) return;
  input.type = (input.type === 'password') ? 'text' : 'password';
}
</script>
</body>
</html>
