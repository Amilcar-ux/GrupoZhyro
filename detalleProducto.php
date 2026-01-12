<?php
session_start();

// 1) Validar parámetro id
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(400);
    echo "Falta parámetro id o es inválido";
    exit;
}
$idProducto = (int)$_GET['id'];

// 2) Conexión a BD (ajusta si tu clave es distinta)
$dsn  = 'mysql:host=localhost;dbname=TiendaRopa;charset=utf8';
$user = 'root';
$pass = 'root';

$detalleProducto = null;

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2.1 Datos básicos
    $sqlProd = "SELECT nombre, descripcion, precio, imagen
                FROM producto
                WHERE ID_producto = ?";
    $stmtProd = $pdo->prepare($sqlProd);
    $stmtProd->execute([$idProducto]);
    $rowProd = $stmtProd->fetch(PDO::FETCH_ASSOC);

    if (!$rowProd) {
        http_response_code(404);
        echo "Producto no encontrado";
        exit;
    }

    // 2.2 Colores
    $sqlCol = "SELECT c.nombre_color
               FROM color c
               INNER JOIN producto_color pc ON c.ID_color = pc.ID_color
               WHERE pc.ID_producto = ?";
    $stmtCol = $pdo->prepare($sqlCol);
    $stmtCol->execute([$idProducto]);
    $colores = $stmtCol->fetchAll(PDO::FETCH_COLUMN);

    // 2.3 Tallas
    $sqlTallas = "SELECT t.nombre_talla
                  FROM talla t
                  INNER JOIN producto_talla pt ON t.ID_talla = pt.ID_talla
                  WHERE pt.ID_producto = ?";
    $stmtTallas = $pdo->prepare($sqlTallas);
    $stmtTallas->execute([$idProducto]);
    $tallas = $stmtTallas->fetchAll(PDO::FETCH_COLUMN);

    // 2.4 Materiales
    $sqlMat = "SELECT m.nombre_material
               FROM material m
               INNER JOIN producto_material pm ON m.ID_material = pm.ID_material
               WHERE pm.ID_producto = ?";
    $stmtMat = $pdo->prepare($sqlMat);
    $stmtMat->execute([$idProducto]);
    $materiales = $stmtMat->fetchAll(PDO::FETCH_COLUMN);

    // 2.5 Guía de cuidados
    $sqlGuia = "SELECT instrucciones
                FROM guia_cuidados
                WHERE ID_producto = ?";
    $stmtGuia = $pdo->prepare($sqlGuia);
    $stmtGuia->execute([$idProducto]);
    $rowGuia = $stmtGuia->fetch(PDO::FETCH_ASSOC);
    $guiaCuidados = $rowGuia ? $rowGuia['instrucciones'] : '';

    // 2.6 Opiniones
    $sqlOpiniones = "SELECT ID_opinion, ID_producto, ID_cliente, comentario, calificacion, fecha
                     FROM opinion
                     WHERE ID_producto = ?";
    $stmtOpiniones = $pdo->prepare($sqlOpiniones);
    $stmtOpiniones->execute([$idProducto]);
    $opiniones = $stmtOpiniones->fetchAll(PDO::FETCH_ASSOC);

    // 2.7 Imágenes por color
    $sqlImgs = "SELECT c.nombre_color, pci.imagen_url
                FROM producto_color_imagenes pci
                JOIN color c ON pci.ID_color = c.ID_color
                WHERE pci.ID_producto = ?";
    $stmtImgs = $pdo->prepare($sqlImgs);
    $stmtImgs->execute([$idProducto]);
    $imagenesPorColor = [];
    while ($rowImg = $stmtImgs->fetch(PDO::FETCH_ASSOC)) {
        $colorName = $rowImg['nombre_color'];
        $urlImagen = $rowImg['imagen_url'];
        if (!isset($imagenesPorColor[$colorName])) {
            $imagenesPorColor[$colorName] = [];
        }
        $imagenesPorColor[$colorName][] = $urlImagen;
    }

    // 2.8 Armar array detalleProducto (equivalente al objeto Java)
    $detalleProducto = [
        'id'              => $idProducto,
        'nombre'          => $rowProd['nombre'],
        'descripcion'     => $rowProd['descripcion'],
        'precio'          => $rowProd['precio'],
        'imagen'          => $rowProd['imagen'],
        'colores'         => $colores,
        'tallas'          => $tallas,
        'materiales'      => $materiales,
        'guiaCuidados'    => $guiaCuidados,
        'opiniones'       => $opiniones,
        'imagenesPorColor'=> $imagenesPorColor
    ];

} catch (PDOException $e) {
    http_response_code(500);
    echo "Error de base de datos: " . $e->getMessage();
    exit;
}

// Usuario en sesión
$nombreUsuario = isset($_SESSION['usuario']['nombre'])
    ? $_SESSION['usuario']['nombre']
    : null;

// helpers para vistas
function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta charset="UTF-8" />
  <title><?php echo esc($detalleProducto['nombre']); ?></title>
  <link rel="stylesheet" href="css/detalleProducto.css" />
  <link rel="stylesheet" href="css/index.css" />
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

<main class="detalle-producto-container">
    <!-- COLUMNA IZQUIERDA: IMAGEN -->
    <div class="imagen-container">
        <?php
        $imgPrincipal = $detalleProducto['imagen'];

        if (empty($imgPrincipal) && !empty($detalleProducto['colores'])) {
            $primerColor = $detalleProducto['colores'][0];
            if (!empty($detalleProducto['imagenesPorColor'][$primerColor])) {
                $imgPrincipal = $detalleProducto['imagenesPorColor'][$primerColor][0];
            }
        }

        if (empty($imgPrincipal)) {
            $imgPrincipal = 'images/no-image.png';
        }
        ?>
        <img id="imagenPrincipal"
             src="<?php echo esc($imgPrincipal); ?>"
             alt="<?php echo esc($detalleProducto['nombre']); ?>" />

        <button class="favorito" type="button"
                onclick="agregarFavorito(<?php echo (int)$detalleProducto['id']; ?>)">❤</button>

        <!-- Miniaturas por color -->
        <?php if (!empty($detalleProducto['colores'])): ?>
            <?php foreach ($detalleProducto['colores'] as $color): ?>
                <?php
                  $colorId = str_replace(' ', '-', $color);
                  $imgsColor = $detalleProducto['imagenesPorColor'][$color] ?? [];
                ?>
                <div id="miniaturas-<?php echo esc($colorId); ?>"
                     class="miniaturas-por-color"
                     data-color="<?php echo esc($color); ?>">
                    <?php if (!empty($imgsColor)): ?>
                        <?php foreach ($imgsColor as $img): ?>
                            <img src="<?php echo esc($img); ?>"
                                 alt="<?php echo esc($color); ?>"
                                 class="miniatura-color"
                                 onclick="mostrarImagen('<?php echo esc($img); ?>')"
                                 onerror="this.style.display='none'" />
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- COLUMNA DERECHA: INFO PRODUCTO -->
    <div class="info-container">

        <!-- Colores -->
        <?php if (!empty($detalleProducto['colores'])): ?>
            <div class="opciones-colores">
                <strong>Color:</strong>
                <?php foreach ($detalleProducto['colores'] as $idx => $color): ?>
                    <label class="color-option" data-color="<?php echo esc($color); ?>">
                        <input type="radio"
                               name="color"
                               value="<?php echo esc($color); ?>"
                               id="color-<?php echo $idx; ?>"
                               onchange="seleccionarColor('<?php echo esc($color); ?>')"
                            <?php echo $idx === 0 ? 'checked' : ''; ?> />
                        <span class="color-bolita"></span>
                        <span class="color-texto"><?php echo esc($color); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h1><?php echo esc($detalleProducto['nombre']); ?></h1>
        <p class="descripcion"><?php echo esc($detalleProducto['descripcion']); ?></p>

        <!-- Formulario carrito -->
        <form action="agregarCarrito.php" method="post" class="form-carrito">
            <input type="hidden" name="idProducto" value="<?php echo (int)$detalleProducto['id']; ?>" />
            <input type="hidden" name="color" id="colorSeleccionado"
                   value="<?php echo !empty($detalleProducto['colores']) ? esc($detalleProducto['colores'][0]) : ''; ?>" />
            <input type="hidden" name="talla" id="tallaSeleccionada" />

            <!-- Tallas -->
            <?php if (!empty($detalleProducto['tallas'])): ?>
                <div class="opciones-tallas">
                    <strong>Talla:</strong>
                    <?php foreach ($detalleProducto['tallas'] as $idx => $talla): ?>
                        <label class="talla-label">
                            <input type="radio"
                                   name="talla-radio"
                                   value="<?php echo esc($talla); ?>"
                                   onchange="seleccionarTalla('<?php echo esc($talla); ?>')"
                                <?php echo $idx === 0 ? 'checked' : ''; ?> />
                            <span class="talla-option"><?php echo esc($talla); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="precio-botones">
                <p class="precio">S/ <?php echo number_format($detalleProducto['precio'], 2); ?></p>
                <button type="submit" id="btnAñadir" class="btn-anadir">Añadir al carrito</button>
            </div>
        </form>

        <!-- Detalles extra -->
        <div class="detalles-extra">
            <!-- Opiniones -->
            <details>
                <?php
                  $totalOpiniones = count($detalleProducto['opiniones']);
                  $suma = 0;
                  foreach ($detalleProducto['opiniones'] as $op) {
                      $suma += (int)$op['calificacion'];
                  }
                  $promedio = $totalOpiniones > 0 ? $suma / $totalOpiniones : 0;
                ?>
                <summary>Opiniones (<?php echo $totalOpiniones; ?>)</summary>
                <div class="valoracion">
                    <div class="estrellas">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span><?php echo $i <= $promedio ? '★' : '☆'; ?></span>
                        <?php endfor; ?>
                        <span>(<?php echo number_format($promedio, 1); ?>/5)</span>
                    </div>
                    <div class="comentarios">
                        <?php if ($totalOpiniones > 0): ?>
                            <?php foreach ($detalleProducto['opiniones'] as $op): ?>
                                <p>"<?php echo esc($op['comentario']); ?>"</p>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p><em>No hay opiniones aún.</em></p>
                        <?php endif; ?>
                    </div>
                </div>
            </details>

            <!-- Materiales -->
            <details>
                <summary>Materiales</summary>
                <?php if (!empty($detalleProducto['materiales'])): ?>
                    <?php foreach ($detalleProducto['materiales'] as $m): ?>
                        <p>• <?php echo esc($m); ?></p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><em>No especificados</em></p>
                <?php endif; ?>
            </details>

            <!-- Guía de cuidados -->
            <details>
                <summary>Guía de cuidados</summary>
                <p><?php echo nl2br(esc($detalleProducto['guiaCuidados'])); ?></p>
            </details>
        </div>
    </div>
</main>

<script>
// Misma lógica JS que tenías en el JSP
function colorNombreAHex(nombreColor) {
    const colores = {
        'Negro': '#000000',
        'Blanco': '#FFFFFF',
        'Rojo': '#FF0000',
        'Azul': '#0000FF',
        'Verde': '#00FF00',
        'Gris': '#808080',
        'Amarillo': '#FFFF00',
        'Naranja': '#FFA500',
        'Morado': '#800080',
        'Rosa': '#FFC0CB',
        'Beige': '#F5F5DC',
        'Marrón': '#A52A2A',
        'Plateado': '#C0C0C0',
        'Dorado': '#FFD700',
        'Azul Marino': '#000080',
        'Verde Oliva': '#808000'
    };
    return colores[nombreColor] || '#666666';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.color-option').forEach(option => {
        const color = option.dataset.color;
        const bolita = option.querySelector('.color-bolita');
        if (color && bolita) {
            bolita.style.backgroundColor = colorNombreAHex(color);
        }
    });

    const primerColorInput = document.querySelector('input[name="color"]:checked');
    if (primerColorInput) {
        seleccionarColor(primerColorInput.value);
    }
});

function seleccionarColor(color) {
    document.querySelectorAll('.miniaturas-por-color').forEach(el => el.style.display = 'none');

    const contenedorId = 'miniaturas-' + color.replace(/\s+/g, '-');
    const contenedor = document.getElementById(contenedorId);

    if (contenedor) {
        contenedor.style.display = 'flex';
        const primeraImg = contenedor.querySelector('.miniatura-color');
        if (primeraImg) {
            mostrarImagen(primeraImg.src);
        }
    }
    document.getElementById('colorSeleccionado').value = color;
}

function mostrarImagen(url) {
    document.getElementById('imagenPrincipal').src = url;
}

function seleccionarTalla(talla) {
    document.getElementById('tallaSeleccionada').value = talla;
}

function agregarFavorito(idProducto) {
    fetch('agregarFavorito.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + encodeURIComponent(idProducto)
    })
    .then(resp => resp.json())
    .then(data => alert(data.success ? 'Añadido a favoritos ❤️' : 'Error al añadir'))
    .catch(err => alert('Error: ' + err.message));
}
</script>

<?php include 'foter.php'; ?>
</body>
</html>

