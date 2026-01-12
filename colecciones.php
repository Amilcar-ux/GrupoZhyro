<?php
session_start();

$nombreUsuario = isset($_SESSION['usuario']['nombre'])
    ? $_SESSION['usuario']['nombre']
    : null;

// Parámetro de colección
$coleccion = isset($_GET['c']) ? trim($_GET['c']) : '';

// Conexión BD
$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$user = 'root';
$pass = 'root';

function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($coleccion === '') {
        // VISTA 1: LISTA DE COLECCIONES
        $sql = "SELECT DISTINCT coleccion
                FROM producto
                WHERE estado = 'Activo' AND coleccion IS NOT NULL";
        $stmt = $pdo->query($sql);
        $colecciones = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // ==== VISTA colecciones.jsp ====
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
            <!-- TÍTULO TIPO CATALOG -->
            <section class="promo-header">
                <div class="promo-header-text">
                    <h1 class="promo-title">Catalog</h1>
                    <p class="promo-subtitle">
                        Nuestras colecciones destacadas.
                    </p>
                </div>
            </section>

            <!-- SELECTOR DE COLECCIÓN -->
            <section class="colecciones-selector">
                <label for="selectColeccion" class="colecciones-label">
                    Ver colección:
                </label>
                <div class="colecciones-select-wrapper">
                    <select id="selectColeccion" name="coleccion" class="colecciones-select">
                        <option value="">-- Selecciona una colección --</option>
                        <?php foreach ($colecciones as $col): ?>
                            <option value="<?php echo esc($col); ?>">
                                <?php echo esc($col); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </section>

            <script>
            document.addEventListener("DOMContentLoaded", function() {
                const select = document.getElementById("selectColeccion");
                select.addEventListener("change", function() {
                    const val = this.value;
                    if (val) {
                        window.location.href = "colecciones.php?c=" + encodeURIComponent(val);
                    }
                });
            });
            </script>

            <!-- TARJETAS DE COLECCIONES -->
            <section class="colecciones-grid">
                <?php foreach ($colecciones as $col): ?>
                    <article class="tarjeta-coleccion">
                        <a href="colecciones.php?c=<?php echo urlencode($col); ?>">
                            <div class="tarjeta-coleccion-img">
                                <img src="images/colecciones/<?php echo esc($col); ?>.jpg"
                                     alt="<?php echo esc($col); ?>">
                            </div>
                            <div class="tarjeta-coleccion-texto">
                                <h2><?php echo esc($col); ?></h2>
                                <p>Colección <?php echo esc($col); ?></p>
                                <button type="button" class="btn-detalle">
                                    Compra ahora
                                </button>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </section>
        </main>

        <?php include 'foter.php'; ?>
        </body>
        </html>
        <?php
        exit;

    } else {
        // VISTA 2: PRODUCTOS DE ESA COLECCIÓN
        $sql = "SELECT ID_producto, nombre, precio, imagen
                FROM producto
                WHERE estado = 'Activo' AND coleccion = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$coleccion]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Delegamos la vista al archivo colecciones_polos.php
        // para no mezclar demasiado código aquí.
        // Las variables $productos y $coleccion quedan disponibles.
        include 'colecciones_polos.php';
        exit;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo "Error de base de datos: " . $e->getMessage();
    exit;
}
