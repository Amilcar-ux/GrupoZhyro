<?php
// inventory.php
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

// Filtros GET
$searchName   = $_GET['searchName']   ?? null;
$filterEstado = $_GET['filterEstado'] ?? null;

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Nombre admin
    $psNom = $pdo->prepare(
        'SELECT a.nombre 
         FROM administrador a 
         JOIN usuario u ON a.ID_usuario = u.ID_usuario 
         WHERE u.email = ?'
    );
    $psNom->execute([$emailSession]);
    if ($rowNom = $psNom->fetch(PDO::FETCH_ASSOC)) {
        $nombreAdmin = $rowNom['nombre'];
    } else {
        $nombreAdmin = $emailSession;
    }

    // Construcción dinámica de la consulta
    $sql = "SELECT ID_producto, nombre, descripcion, precio, stock, stock_minimo,
                   imagen, estado, fecha_actualizacion_stock
            FROM producto
            WHERE 1=1";
    $params = [];

    if ($searchName !== null && trim($searchName) !== '') {
        $sql      .= " AND nombre LIKE ?";
        $params[]  = "%" . $searchName . "%";
    }
    if ($filterEstado !== null && trim($filterEstado) !== '') {
        $sql      .= " AND estado = ?";
        $params[]  = $filterEstado;
    }
    $sql .= " ORDER BY ID_producto";

    $ps = $pdo->prepare($sql);
    $ps->execute($params);
    $productos = $ps->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $nombreAdmin = $emailSession;
    $productos   = [];
    $errorMsg    = 'Error cargando inventario: ' . $e->getMessage();
}

// Mensajes de éxito/error vía GET/SESSION (adaptación del JSP)
$success = $_GET['success'] ?? null;
$error   = $errorMsg ?? ($_SESSION['inventory_error'] ?? null);
unset($_SESSION['inventory_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Panel de Administración - Inventario</title>
    <link rel="stylesheet" href="css/inventory.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
                        <i class="material-icons">work</i> ADMINISTRATION 
                        <i class="material-icons dropdown-icon">arrow_drop_down</i>
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
                        <i class="material-icons">people</i> USERS 
                        <i class="material-icons dropdown-icon">arrow_drop_down</i>
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
                <img src="assets/img/avatar-male.png" alt="avatar" class="avatar-small">
            </div>
        </header>

        <?php if ($success !== null): ?>
            <div class="alert alert-success">Inventario actualizado correctamente</div>
        <?php elseif ($error !== null): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="filterForm" method="GET" action="inventory.php" style="margin-bottom: 20px; display:flex; gap:10px; align-items: center;">
            <input type="text" name="searchName" placeholder="Buscar por nombre"
                   value="<?= htmlspecialchars($searchName ?? '') ?>" 
                   style="padding:6px; font-size:15px;"/>
            <select name="filterEstado" style="padding:6px; font-size:15px;">
                <option value="" <?= ($filterEstado === null || $filterEstado === '') ? 'selected' : '' ?>>Todos estados</option>
                <option value="Activo"   <?= ($filterEstado === 'Activo')   ? 'selected' : '' ?>>Activo</option>
                <option value="Inactivo" <?= ($filterEstado === 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
            </select>
            <button type="submit" class="btn-primary">Filtrar</button>
        </form>

        <section class="inventory-section">
            <h2>Inventario de Productos</h2>
            <button class="btn-primary" onclick="window.print()">
                <i class="material-icons">print</i> Descargar inventario
            </button>

            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio (S/)</th>
                        <th>Stock</th>
                        <th>Stock mínimo alerta</th>
                        <th>Estado</th>
                        <th>Fecha última actualización</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($productos)): ?>
                    <tr><td colspan="10">No hay productos que coincidan con el filtro.</td></tr>
                <?php else: ?>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <form action="UpdateInventory.php" method="POST">
                            <td>
                                <?php if (!empty($p['imagen'])): ?>
                                    <img src="images/<?= htmlspecialchars($p['imagen']) ?>" alt="Imagen producto" width="80" />
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <input type="hidden" name="ID_producto" value="<?= (int)$p['ID_producto'] ?>" />
                            <td><?= (int)$p['ID_producto'] ?></td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td><?= htmlspecialchars($p['descripcion']) ?></td>
                            <td>
                                <input type="number" name="precio" 
                                       value="<?= htmlspecialchars($p['precio']) ?>" 
                                       step="0.01" min="0" />
                            </td>
                            <?php
                                $stock       = (int)$p['stock'];
                                $stockMin    = (int)$p['stock_minimo'];
                                $isLowStock  = $stock <= $stockMin;
                            ?>
                            <td <?= $isLowStock ? "style='background:#ffe6e6; color:#a40000;'" : '' ?>>
                                <input type="number" name="stock" value="<?= $stock ?>" min="0" />
                                <?php if ($isLowStock): ?>
                                    <span title="Stock bajo" style="margin-left:6px;color:#a40000;font-size:18px;">⚠</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="number" name="stock_minimo" value="<?= $stockMin ?>" min="0" />
                            </td>
                            <td>
                                <select name="estado">
                                    <option value="Activo"   <?= ($p['estado'] === 'Activo')   ? 'selected' : '' ?>>Activo</option>
                                    <option value="Inactivo" <?= ($p['estado'] === 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </td>
                            <td><?= htmlspecialchars($p['fecha_actualizacion_stock']) ?></td>
                            <td><button type="submit">Guardar</button></td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        document.getElementById("adminDropdownBtn").addEventListener("click", function(e){
            e.preventDefault();
            const dropdown = document.getElementById("adminDropdownContent");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        });
        document.getElementById("usersDropdownBtn").addEventListener("click", function(e){
            e.preventDefault();
            const dropdown = document.getElementById("usersDropdownContent");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        });
        window.onclick = function(event){
            if(!event.target.matches('#adminDropdownBtn') && !event.target.closest('.dropdown')){
                const dropdown = document.getElementById("adminDropdownContent");
                if(dropdown) dropdown.style.display = "none";
            }
            if(!event.target.matches('#usersDropdownBtn') && !event.target.closest('.dropdown')){
                const dropdown2 = document.getElementById("usersDropdownContent");
                if(dropdown2) dropdown2.style.display = "none";
            }
        };
    </script>
</body>
</html>
