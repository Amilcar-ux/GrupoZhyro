<?php
session_start();

// Usuario en sesión (mismo esquema que en otras páginas)
$nombreUsuario = isset($_SESSION['usuario']['nombre'])
    ? $_SESSION['usuario']['nombre']
    : null;

// 1) Leer parámetro de orden
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'mas_vendidos';

$orderBy = "p.ID_producto DESC"; // default
switch ($orden) {
    case 'az':             $orderBy = "p.nombre ASC"; break;
    case 'za':             $orderBy = "p.nombre DESC"; break;
    case 'precio_asc':     $orderBy = "p.precio ASC"; break;
    case 'precio_desc':    $orderBy = "p.precio DESC"; break;
    case 'fecha_antigua':  $orderBy = "p.ID_producto ASC"; break;
    case 'fecha_reciente': $orderBy = "p.ID_producto DESC"; break;
    case 'mas_vendidos':
    default:
        $orderBy = "p.ID_producto DESC";
        break;
}

// 2) Conexión y consulta a la BD
$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$user = 'root';
$pass = 'root';

$bestsellers = [];
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT p.ID_producto, p.nombre, p.precio, p.imagen
            FROM producto p
            WHERE p.estado = 'Activo' AND p.best_seller = 1
            ORDER BY $orderBy";

    $stmt = $pdo->query($sql);
    $bestsellers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // En producción podrías loguear esto en lugar de mostrarlo
    $error = "Error de base de datos: " . $e->getMessage();
}

// helper para escapar
function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Más vendidos | Tienda de Ropa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/colecciones.css">
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

<main>
    <!-- HEADER PEQUEÑO SIN BANNER -->
    <section class="promo-header">
        <div class="promo-header-text">
            <h1 class="promo-title">BEST SELLERS</h1>
            <p class="promo-subtitle">
                Nuestros productos TOP ventas, juntos en un mismo lugar.
            </p>
        </div>
    </section>

    <section class="productos productos-coleccion">
        <header class="bests-header">
            <h2>Los más vendidos</h2>

            <!-- Filtro -->
            <form action="bestsellers.php" method="get" class="bests-filtro">
                <label for="orden">Ordenar por</label>
                <select id="orden" name="orden">
                    <option value="mas_vendidos" <?php echo $orden === 'mas_vendidos' ? 'selected' : ''; ?>>Más vendidos</option>
                    <option value="az" <?php echo $orden === 'az' ? 'selected' : ''; ?>>Alfabéticamente, A-Z</option>
                    <option value="za" <?php echo $orden === 'za' ? 'selected' : ''; ?>>Alfabéticamente, Z-A</option>
                    <option value="precio_asc" <?php echo $orden === 'precio_asc' ? 'selected' : ''; ?>>Precio, menor a mayor</option>
                    <option value="precio_desc" <?php echo $orden === 'precio_desc' ? 'selected' : ''; ?>>Precio, mayor a menor</option>
                    <option value="fecha_antigua" <?php echo $orden === 'fecha_antigua' ? 'selected' : ''; ?>>Fecha: antiguo(a) a reciente</option>
                    <option value="fecha_reciente" <?php echo $orden === 'fecha_reciente' ? 'selected' : ''; ?>>Fecha: reciente a antiguo(a)</option>
                </select>
                <button type="submit">Ordenar</button>
            </form>
        </header>

        <div class="grid-productos">
            <?php if (!empty($bestsellers)): ?>
                <?php foreach ($bestsellers as $p): ?>
                    <article class="producto">
                        <div class="producto-img-wrapper">
                            <img src="images/<?php echo esc($p['imagen']); ?>"
                                 alt="<?php echo esc($p['nombre']); ?>">
                            <span class="badge-best">TOP VENTAS</span>
                        </div>
                        <h3><?php echo esc($p['nombre']); ?></h3>
                        <p class="precio">S/ <?php echo number_format($p['precio'], 2); ?></p>
                        <a href="detalleProducto.php?id=<?php echo (int)$p['ID_producto']; ?>">
                            <button class="btn-detalle">
                                Ver detalle
                            </button>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="vacio">
                    <h3>No hay productos marcados como Best Seller</h3>
                    <p>Los administradores aún no han seleccionado los productos más vendidos.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'foter.php'; ?>

<a href="https://wa.me/51923932945" class="whatsapp-float"
   target="_blank" rel="noopener" aria-label="Whatsapp">
  <img src="images/whatsapp.png" alt="WhatsApp" />
</a>
</body>
</html>
