<?php
session_start();

$nombreUsuario = isset($_SESSION['usuario']['nombre'])
    ? $_SESSION['usuario']['nombre']
    : null;

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$user = 'root';
$pass = 'root';

$promoActual = isset($_GET['p']) ? trim($_GET['p']) : '';

$promos = [];
$tiposPromo = [];
$error = null;

function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1) Obtener lista de tipos de promo distintos
    $sqlTipos = "SELECT DISTINCT promo
                 FROM producto
                 WHERE estado = 'Activo'
                   AND promo IS NOT NULL
                   AND TRIM(promo) != ''";
    $stmtTipos = $pdo->query($sqlTipos);
    $tiposPromo = $stmtTipos->fetchAll(PDO::FETCH_COLUMN);

    // 2) Obtener productos en promo (filtrados o todos)
    if ($promoActual !== '') {
        $sql = "SELECT ID_producto, nombre, precio, imagen
                FROM producto
                WHERE estado = 'Activo' AND promo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$promoActual]);
    } else {
        $sql = "SELECT ID_producto, nombre, precio, imagen
                FROM producto
                WHERE estado = 'Activo'
                  AND promo IS NOT NULL
                  AND TRIM(promo) != ''";
        $stmt = $pdo->query($sql);
    }

    $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error de base de datos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Promociones | Tienda de Ropa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/promos.css">
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

    <!-- CABECERA -->
    <section class="promo-header">
        <div class="promo-header-text">
            <h1 class="promo-title">PROMOCIONES</h1>
            <p class="promo-subtitle">
                Nuestros productos en promoción, todos juntos en un mismo lugar.
            </p>
        </div>
    </section>

    <!-- FILTROS DINÁMICOS PROMOS -->
    <div class="promo-filtros">
        <?php foreach ($tiposPromo as $promo): ?>
            <a href="promos.php?p=<?php echo urlencode($promo); ?>"
               class="<?php echo ($promoActual === $promo) ? 'filtro-promo active' : 'filtro-promo'; ?>">
                <?php echo esc($promo); ?>
            </a>
        <?php endforeach; ?>

        <a href="promos.php"
           class="<?php echo ($promoActual === '' ? 'filtro-promo active' : 'filtro-promo'); ?>">
            Todas las promos
        </a>
    </div>

    <!-- LISTA DE PRODUCTOS EN PROMO -->
    <section class="productos productos-promos">
        <div class="grid-productos">

            <?php if (!empty($error)): ?>
                <div class="sin-promos">
                    <h3><?php echo esc($error); ?></h3>
                </div>
            <?php elseif (!empty($promos)): ?>
                <?php foreach ($promos as $p): ?>
                    <article class="producto">
                        <div class="producto-img-wrapper">
                            <img src="images/<?php echo esc($p['imagen']); ?>"
                                 alt="<?php echo esc($p['nombre']); ?>">
                            <span class="promo-badge">¡EN PROMO!</span>
                        </div>
                        <h3 class="producto-nombre"><?php echo esc($p['nombre']); ?></h3>
                        <p class="producto-precio">
                            S/ <?php echo number_format($p['precio'], 2); ?>
                        </p>
                        <a href="detalleProducto.php?id=<?php echo (int)$p['ID_producto']; ?>">
                            <button class="btn-detalle">Ver detalle</button>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="sin-promos">
                    <h3>No hay promociones activas</h3>
                    <p>Los administradores aún no han creado promociones.</p>
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
