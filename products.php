<?php
// products.php
session_start();

$userType = $_SESSION['userType'] ?? null;
if ($userType === null || $userType !== 'Administrador') {
    header('Location: indexAdmin.php');
    exit;
}

$emailSession = $_SESSION['userName'] ?? '';
$nombreAdmin  = '';

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $psHead = $pdo->prepare(
        'SELECT a.nombre 
         FROM administrador a 
         JOIN usuario u ON a.ID_usuario = u.ID_usuario 
         WHERE u.email = ?'
    );
    $psHead->execute([$emailSession]);
    if ($row = $psHead->fetch(PDO::FETCH_ASSOC)) {
        $nombreAdmin = $row['nombre'];
    } else {
        $nombreAdmin = $emailSession;
    }

    // Datos para selects del formulario NEW
    $colores = $pdo->query('SELECT ID_color, nombre_color FROM color')->fetchAll(PDO::FETCH_ASSOC);
    $tallas  = $pdo->query('SELECT ID_talla, nombre_talla FROM talla')->fetchAll(PDO::FETCH_ASSOC);
    $mats    = $pdo->query('SELECT ID_material, nombre_material FROM material')->fetchAll(PDO::FETCH_ASSOC);

    // Productos para LIST
    $sqlProd = "SELECT p.ID_producto, p.nombre, p.descripcion, p.precio, p.stock, p.imagen AS imagen_principal,
                       p.estado, p.coleccion, p.promo, p.best_seller,
                       c.ID_color, c.nombre_color, pci.imagen_url
                FROM producto p
                LEFT JOIN producto_color pc 
                    ON p.ID_producto = pc.ID_producto
                LEFT JOIN color c 
                    ON pc.ID_color = c.ID_color
                LEFT JOIN producto_color_imagenes pci 
                    ON pc.ID_producto = pci.ID_producto 
                   AND pc.ID_color = pci.ID_color
                ORDER BY p.ID_producto, c.ID_color";
    $stmtProd = $pdo->query($sqlProd);
    $productosRows = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $nombreAdmin   = $emailSession;
    $colores = $tallas = $mats = $productosRows = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Panel de Administración - Products</title>
    <link rel="stylesheet" href="css/products.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<aside class="sidebar">
    <div class="logo">Inventory</div>
    <div class="profile">
        <img src="assets/img/avatar-male.png" alt="avatar" class="avatar">
        <h3><?= (!empty($nombreAdmin) ? htmlspecialchars($nombreAdmin) : 'Admin') ?></h3>
        <p>Admin</p>
    </div>
    <nav>
        <ul>
            <li><i class="material-icons">dashboard</i> DASHBOARD</li>
            <li><a href="home.php"><i class="material-icons">home</i> HOME</a></li>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn" id="adminDropdownBtn">
                    <i class="material-icons">work</i> ADMINISTRATION <i class="material-icons dropdown-icon">arrow_drop_down</i>
                </a>
                <ul class="dropdown-content" id="adminDropdownContent">
                    <li><a href="company.php">Compañía</a></li>
                    <li><a href="providers.php">Proveedores</a></li>
                    <li><a href="payments.php">Métodos de Pago</a></li>
                    <li><a href="categories.php">Categorías</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn" id="usersDropdownBtn">
                    <i class="material-icons">people</i> USERS <i class="material-icons dropdown-icon">arrow_drop_down</i>
                </a>
                <ul class="dropdown-content" id="usersDropdownContent">
                    <li><a href="admin.php">Administradores</a></li>
                    <li><a href="client.php">Usuarios</a></li>
                </ul>
            </li>
            <li><a href="products.php"><i class="material-icons">shopping_cart</i> PRODUCTS</a></li>
            <li><a href="sales.php"><i class="material-icons">attach_money</i> SALES</a></li>
            <li><a href="inventory.php"><i class="material-icons">inventory</i> INVENTORY</a></li>
            <li><a href="settings.php"><i class="material-icons">settings</i> SETTINGS</a></li>
        </ul>
    </nav>
</aside>

<main class="main-content">
  <header>
      <div class="user-info">
          <i class="material-icons">notifications</i>
          <a href="logout.php" id="logoutBtn" title="Cerrar sesión" style="color:white; cursor:pointer;">Cerrar sesión</a>
          <span><?= htmlspecialchars($nombreAdmin) ?></span>
          <img src="assets/img/avatar-male.png" alt="avatar" class="avatar-small"/>
      </div>
  </header>

  <div class="tabs">
    <button class="tab-btn active" id="showNew">NEW</button>
    <button class="tab-btn" id="showList">LIST</button>
  </div>

  <!-- NEW -->
  <div class="tab-content" id="tabNew">
      <h2>Agregar Nuevo Producto</h2>
      <form action="AddProduct.php" method="POST" enctype="multipart/form-data" class="form-product">
          <label>Nombre:</label>
          <input type="text" name="nombre" required/>

          <label>Descripción:</label>
          <input type="text" name="descripcion" required/>

          <label>Precio:</label>
          <input type="number" step="0.01" name="precio" required/>

          <label>Stock:</label>
          <input type="number" name="stock" required/>

          <label>Imagen Principal:</label>
          <input type="file" name="imagen" accept="image/*" />

          <label>Colores (múltiples con CTRL):</label>
          <select name="colores[]" id="coloresSelect" multiple onchange="mostrarInputsImagenes()">
              <?php foreach ($colores as $c): ?>
                  <option value="<?= (int)$c['ID_color'] ?>">
                      <?= htmlspecialchars($c['nombre_color']) ?>
                  </option>
              <?php endforeach; ?>
          </select>

          <div id="imagenesColoresContainer"></div>

          <label>Tallas (múltiples con CTRL):</label>
          <select name="tallas[]" multiple>
              <?php foreach ($tallas as $t): ?>
                  <option value="<?= (int)$t['ID_talla'] ?>">
                      <?= htmlspecialchars($t['nombre_talla']) ?>
                  </option>
              <?php endforeach; ?>
          </select>

          <label>Materiales (múltiples con CTRL):</label>
          <select name="materiales[]" multiple>
              <?php foreach ($mats as $m): ?>
                  <option value="<?= (int)$m['ID_material'] ?>">
                      <?= htmlspecialchars($m['nombre_material']) ?>
                  </option>
              <?php endforeach; ?>
          </select>

          <label>Guía de Cuidados:</label>
          <input type="text" name="guia_cuidados" placeholder="Instrucciones lavado...">

          <label>Estado del producto:</label>
          <select name="estado">
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
          </select>

          <label>Colección:</label>
          <select name="coleccion">
              <option value="">(Sin colección)</option>
              <option value="street">Streetwear</option>
              <option value="minimal">Minimal</option>
              <option value="graficos">Gráficos</option>
          </select>

          <label>Tipo de promo:</label>
          <select name="promo">
              <option value="">Sin promo</option>
              <option value="2x1">2 x 1</option>
              <option value="3x1">3 x 1</option>
              <option value="descuento">Descuento</option>
          </select>

          <label>¿Best Seller?</label>
          <select name="bestSeller">
              <option value="0">No</option>
              <option value="1">Sí</option>
          </select>

          <button type="submit">Agregar</button>
      </form>
  </div>

  <!-- LIST -->
  <div class="tab-content" id="tabList" style="display:none;">
      <h2>Listado de Productos</h2>
      <div class="tiles-container">
        <?php
        // Recorremos las filas agrupando por producto
        $lastProductId = -1;
        foreach ($productosRows as $row) {
            $currentProductId = (int)$row['ID_producto'];
            if ($currentProductId !== $lastProductId) {
                if ($lastProductId !== -1) {
                    echo "</div>"; // cierre colors-container
                    echo "<button type='submit'>Guardar Cambios</button>";
                    echo "</form>";
                    echo "</div>";
                }

                $imgPrincipal = htmlspecialchars($row['imagen_principal'] ?? '');
                $nombre       = htmlspecialchars($row['nombre'] ?? '');
                $descripcion  = htmlspecialchars($row['descripcion'] ?? '');
                $precio       = $row['precio'];
                $stock        = (int)$row['stock'];
                $estado       = $row['estado'];
                $coleccion    = $row['coleccion'];
                $promo        = $row['promo'];
                $best         = (int)$row['best_seller'];

                echo "<div class='tile-product'>";
                echo "<form action='EditProduct.php' method='POST' enctype='multipart/form-data'>";
                echo "<input type='hidden' name='ID_producto' value='{$currentProductId}'/>";

                echo "<img src='images/{$imgPrincipal}' alt='imagen' class='product-img'>";
                echo "<label>Nombre:</label><input type='text' name='nombre' value='{$nombre}' required/>";
                echo "<label>Descripción:</label><input type='text' name='descripcion' value='{$descripcion}' required/>";
                echo "<label>Precio:</label><input type='number' step='0.01' name='precio' value='{$precio}' required/>";
                echo "<label>Stock:</label><input type='number' name='stock' value='{$stock}' required/>";

                echo "<label>Estado:</label>";
                echo "<select name='estado'>";
                echo "<option value='Activo'".($estado==='Activo'?' selected':'').">Activo</option>";
                echo "<option value='Inactivo'".($estado==='Inactivo'?' selected':'').">Inactivo</option>";
                echo "</select>";

                echo "<label>Colección:</label>";
                echo "<select name='coleccion'>";
                echo "<option value=''".(($coleccion===null || $coleccion==='')?' selected':'').">(Sin colección)</option>";
                echo "<option value='street'".($coleccion==='street'?' selected':'').">Streetwear</option>";
                echo "<option value='minimal'".($coleccion==='minimal'?' selected':'').">Minimal</option>";
                echo "<option value='graficos'".($coleccion==='graficos'?' selected':'').">Gráficos</option>";
                echo "</select>";

                echo "<label>Promo:</label>";
                echo "<select name='promo'>";
                echo "<option value=''".(($promo===null || $promo==='')?' selected':'').">Sin promo</option>";
                echo "<option value='2x1'".($promo==='2x1'?' selected':'').">2 x 1</option>";
                echo "<option value='3x1'".($promo==='3x1'?' selected':'').">3 x 1</option>";
                echo "<option value='descuento'".($promo==='descuento'?' selected':'').">Descuento</option>";
                echo "</select>";

                echo "<label>Best Seller:</label>";
                echo "<select name='bestSeller'>";
                echo "<option value='0'".($best===0?' selected':'').">No</option>";
                echo "<option value='1'".($best===1?' selected':'').">Sí</option>";
                echo "</select>";

                echo "<label>Imagen Principal Actual:</label><br>
                      <img src='images/{$imgPrincipal}' style='width:100px;'/><br>";
                echo "<label>Cambiar Imagen Principal:</label>
                      <input type='file' name='imagen' accept='image/*'/>";

                echo "<div class='colors-container'><h4>Colores y sus imágenes:</h4>";
                $lastProductId = $currentProductId;
            }

            $imagenColor = $row['imagen_url'];
            if ($imagenColor !== null) {
                $idColor  = (int)$row['ID_color'];
                $nomColor = htmlspecialchars($row['nombre_color'] ?? '');
                $imgCol   = htmlspecialchars($imagenColor);

                echo "<div class='color-item'>";
                echo "<img src='{$imgCol}' alt='Color {$nomColor}' style='width: 50px; height: 50px;'/>";
                echo "<p>{$nomColor}</p>";
                echo "<label>Cambiar imagen para este color:</label>";
                echo "<input type='file' name='imagen_color_{$idColor}' accept='image/*'/>";
                echo "</div>";
            }
        }

        if ($lastProductId !== -1) {
            echo "</div>"; // cierre colors-container
            echo "<button type='submit'>Guardar Cambios</button>";
            echo "</form>";
            echo "</div>";
        }
        ?>
      </div>
  </div>
</main>

<script>
$(document).ready(function(){
    $("#showNew").click(function(){
        $("#tabNew").show();
        $("#tabList").hide();
        $(this).addClass("active");
        $("#showList").removeClass("active");
    });
    $("#showList").click(function(){
        $("#tabNew").hide();
        $("#tabList").show();
        $(this).addClass("active");
        $("#showNew").removeClass("active");
    });
});

function mostrarInputsImagenes() {
    const select = document.getElementById('coloresSelect');
    const container = document.getElementById('imagenesColoresContainer');
    container.innerHTML = '';

    Array.from(select.selectedOptions).forEach(option => {
        const idColor = option.value;
        const label = document.createElement('label');
        label.textContent = 'Imagen para color ' + option.text;

        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'imagen_color_' + idColor;
        input.accept = 'image/*';

        container.appendChild(label);
        container.appendChild(input);
        container.appendChild(document.createElement('br'));
    });
}
</script>
</body>
</html>
