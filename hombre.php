<?php
session_start();

// Conexión a la BD
$dsn  = 'mysql:host=localhost;dbname=TiendaRopa;charset=utf8';
$user = 'root';
$pass = 'root';

$productos = [];
$error     = null;
$mensaje   = null;

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Solo productos Activos
    $sql = "SELECT ID_producto, nombre, imagen, precio
            FROM producto
            WHERE estado = 'Activo'
              AND (nombre LIKE :c1 OR nombre LIKE :c2)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':c1' => '%Camiseta%',
        ':c2' => '%Polo%'
    ]);

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($productos)) {
        $mensaje = "No se encontraron productos.";
    }

} catch (PDOException $e) {
    $error = "Error de base de datos: " . $e->getMessage();
}

// Usuario en sesión
$nombreUsuario = isset($_SESSION['usuario']['nombre'])
    ? $_SESSION['usuario']['nombre']
    : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="UTF-8" />
    <title>Ropa Hombre</title>
    <link rel="stylesheet" href="css/hombre.css" />
    <link rel="stylesheet" href="css/index.css" />
    <script src="js/app.js" defer></script>
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
                    Hola, <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong>
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

<main>
    <div class="main-container">
        <h1>Ropa para Hombre</h1>
        <section>
            <h2>Polos</h2>
            <div class="productos">

                <?php foreach ($productos as $prod): ?>
                    <div class="producto">
                        <img src="images/<?php echo htmlspecialchars($prod['imagen']); ?>"
                             alt="<?php echo htmlspecialchars($prod['nombre']); ?>" />
                        <h3><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                        <p>S/ <?php echo number_format($prod['precio'], 2); ?></p>
                        <a href="detalleProducto.php?id=<?php echo (int)$prod['ID_producto']; ?>"
                           class="btn-carrito">Ver detalle</a>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($error)): ?>
                    <div style="color: red;"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (!empty($mensaje)): ?>
                    <div><?php echo htmlspecialchars($mensaje); ?></div>
                <?php endif; ?>

            </div>
        </section>
    </div>
</main>

<script src="js/hombre.js" defer></script>
<?php include 'foter.php'; ?>
</body>
</html>
