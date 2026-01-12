<?php
session_start();

// Recuperar nombre de usuario desde la sesión (ajusta la clave según cómo la guardes)
$nombreUsuario = isset($_SESSION['usuario']['nombre'])
    ? $_SESSION['usuario']['nombre']
    : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="UTF-8">
    <title>Tienda de Ropa</title>
    <link rel="stylesheet" href="css/index.css">
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
        <!-- BLOQUE USUARIO (texto + botón + icono user) -->
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

            <!-- icono user SIEMPRE al final del bloque usuario -->
            <a href="miCuenta.php">
                <img src="images/user.png" alt="Cuenta">
            </a>
        </div>

        <!-- resto de iconos -->
        <a href="favoritos.php">
            <img src="images/heart.png" alt="Favoritos">
        </a>
        <a href="carrito.php">
            <img src="images/cart.png" alt="Carrito">
        </a>
    </div>
</header>

<main>
    <section class="banner">
        <img src="images/logo.png" alt="Banner Principal">
        <div class="banner-text">
            <h1>Bienvenidos a Nuestra Tienda de Ropa</h1>
            <p>Las mejores ofertas y moda para ti</p>
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
