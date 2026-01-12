<?php
// Este archivo asume que ya existen:
// $productos  -> array de productos
// $coleccion  -> nombre de la colección
// $nombreUsuario -> usuario en sesión
if (!function_exists('esc')) {
    function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Colecciones | Tienda de Ropa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/colecciones.css">
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

<main>
    <!-- HEADER PEQUEÑO SIN BANNER -->
    <section class="promo-header">
        <div class="promo-header-text">
            <h1 class="promo-title">
                <?php echo $coleccion === '' ? 'COLECCIONES' : esc($coleccion); ?>
            </h1>
            <p class="promo-subtitle">
                Descubre los productos de esta colección.
            </p>
        </div>
    </section>

    <section class="productos productos-coleccion">
        <div class="grid-productos">

            <?php if (!empty($productos)): ?>
                <?php foreach ($productos as $p): ?>
                    <article class="producto">
                        <img src="images/<?php echo esc($p['imagen']); ?>"
                             alt="<?php echo esc($p['nombre']); ?>">
                        <h3><?php echo esc($p['nombre']); ?></h3>
                        <p class="precio">
                            S/ <?php echo number_format($p['precio'], 2); ?>
                        </p>
                        <a href="detalleProducto.php?id=<?php echo (int)$p['ID_producto']; ?>">
                            <button class="btn-detalle">
                                Ver detalle
                            </button>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="vacio">
                    <h3>No hay productos en esta colección</h3>
                    <p>Selecciona otra colección o agrega productos en el panel admin.</p>
                </div>
            <?php endif; ?>

        </div>
    </section>
</main>

<?php include 'foter.php'; ?>

<a href="https://wa.me/51923932945" class="whatsapp-float"
   target="_blank" rel="noopener">
  <img src="images/whatsapp.png" alt="WhatsApp" />
</a>
</body>
</html>
