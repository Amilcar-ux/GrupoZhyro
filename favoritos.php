<?php
session_start();

$usuario = $_SESSION['usuario'] ?? null;
if ($usuario === null) {
    $mensaje = urlencode('Debes iniciar sesión para ver tus favoritos');
    header('Location: login.php?msg=' . $mensaje);
    exit;
}

$nombreUsuario = $usuario['nombre'] ?? null;
$favoritosIds = $_SESSION['favoritos'] ?? [];

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$user = 'root';
$pass = 'root';

$productosFavoritos = [];
$errorMsg = null;

function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

if (!empty($favoritosIds)) {
    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Construir IN dinámico: (?, ?, ?)
        $placeholders = implode(',', array_fill(0, count($favoritosIds), '?'));

        $sql = "SELECT ID_producto, nombre, precio, imagen
                FROM producto
                WHERE estado = 'Activo'
                  AND ID_producto IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        // convertir a enteros
        $params = array_map('intval', $favoritosIds);
        $stmt->execute($params);
        $productosFavoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $errorMsg = 'Error de base de datos: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta charset="UTF-8" />
  <title>Mis Favoritos</title>
  <link rel="stylesheet" href="css/index.css" />
  <link rel="stylesheet" href="css/favorito.css" />
</head>
<body>

<header>
    <div class="logo">
        <a href="index.php">
            <img src="images/logo.png" alt="Logo Tienda" />
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
                <img src="images/user.png" alt="Cuenta" />
            </a>
        </div>

        <a href="favoritos.php"><img src="images/heart.png" alt="Favoritos" /></a>
        <a href="carrito.php"><img src="images/cart.png" alt="Carrito" /></a>
    </div>
</header>

<main class="fav-main">
  <h1>Mis Productos Favoritos</h1>

  <?php if ($errorMsg): ?>
      <p class="fav-error"><?php echo esc($errorMsg); ?></p>
  <?php elseif (empty($productosFavoritos)): ?>
      <p>No tienes productos favoritos aún.</p>
      <a href="hombre.php" class="btn">Ver productos</a>
  <?php else: ?>
      <div class="productos">
        <?php foreach ($productosFavoritos as $p): ?>
          <div class="producto">
            <img src="images/<?php echo esc($p['imagen']); ?>"
                 alt="<?php echo esc($p['nombre']); ?>" />
            <h3><?php echo esc($p['nombre']); ?></h3>
            <p>S/ <?php echo number_format($p['precio'], 2); ?></p>
            <a class="btn-carrito"
               href="detalleProducto.php?id=<?php echo (int)$p['ID_producto']; ?>">
               Ver detalle
            </a>
          </div>
        <?php endforeach; ?>
      </div>
  <?php endif; ?>
</main>

<?php include 'foter.php'; ?>
</body>
</html>
