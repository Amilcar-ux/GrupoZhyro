<?php
// shipping.php
session_start();

$userType = $_SESSION['userType'] ?? null;
if ($userType === null || $userType !== 'Administrador') {
    header('Location: indexAdmin.php');
    exit;
}

$email   = $_SESSION['userName'] ?? '';
$nombre  = '';
$message = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

$totalProvincias = 0;

// Obtener nombre admin y total de provincias
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // nombre admin
    $stmt = $pdo->prepare(
        'SELECT a.nombre 
         FROM administrador a 
         JOIN usuario u ON a.ID_usuario = u.ID_usuario 
         WHERE u.email = ?'
    );
    $stmt->execute([$email]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nombre = $row['nombre'];
    } else {
        $nombre = $email;
    }

    // total provincias
    $totalProvincias = (int)$pdo->query('SELECT COUNT(*) FROM provincia')->fetchColumn();

} catch (Exception $e) {
    $nombre = $email;
    $totalProvincias = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Provincias y Envíos - Administración</title>
    <link href="css/shipping.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<aside class="sidebar">
    <div class="logo">🛒 Inventory Pro</div>
    <div class="profile">
        <img src="assets/img/avatar-male.png" alt="avatar" class="avatar"/>
        <h3><?= (!empty($nombre) ? htmlspecialchars($nombre) : 'Admin') ?></h3>
        <p>Administrador</p>
    </div>
    <nav>
        <ul>
            <li><a href="home.php"><i class="material-icons">dashboard</i> Dashboard</a></li>
            <li><a href="home.php"><i class="material-icons">home</i> Inicio</a></li>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">
                    <i class="material-icons">work</i> Administración <i class="material-icons">arrow_drop_down</i>
                </a>
                <ul class="dropdown-content">
                    <li><a href="company.php">🏢 Compañía</a></li>
                    <li><a href="shipping.php">📦 Provincias y Envíos</a></li>
                    <li><a href="payments.php">💳 Métodos de Pago</a></li>
                    <li><a href="categories.php">📂 Categorías</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">
                    <i class="material-icons">people</i> Usuarios <i class="material-icons">arrow_drop_down</i>
                </a>
                <ul class="dropdown-content">
                    <li><a href="admin.php">👨‍💼 Administradores</a></li>
                    <li><a href="client.php">👤 Clientes</a></li>
                </ul>
            </li>
            <li><a href="products.php"><i class="material-icons">shopping_cart</i> Productos</a></li>
            <li><a href="sales.php"><i class="material-icons">attach_money</i> Ventas</a></li>
            <li><a href="inventory.php"><i class="material-icons">inventory</i> Inventario</a></li>
        </ul>
    </nav>
</aside>

<main class="main-content">
<header>
    <div class="user-info">
        <i class="material-icons" style="font-size: 24px; color: #666;">notifications</i>
        <a href="logout.php" title="Cerrar sesión" style="color:#666; text-decoration: none; font-weight: 500;">Cerrar sesión</a>
        <span style="font-weight: 600; color: #333;"><?= htmlspecialchars($nombre) ?></span>
        <img src="assets/img/avatar-male.png" alt="avatar" class="avatar-small"/>
    </div>
</header>

<section class="shipping-section">
    <h2>📦 Gestión de Provincias y Precios de Envío</h2>
    <p class="subtitle">Administra provincias y configura tarifas de envío personalizadas</p>

    <?php if (!empty($message)): ?>
        <div class="message success">✅ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="message error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Pestañas -->
    <div class="shipping-tabs">
        <button class="shipping-tab active" onclick="showTab('provincias')">
            📍 Provincias (<?= $totalProvincias ?>)
        </button>
        <button class="shipping-tab" onclick="showTab('precios')">
            💰 Precios de Envío
        </button>
    </div>

    <!-- Tab Provincias -->
    <div id="provincias" class="tab-content active">
        <!-- Formulario AGREGAR Provincia -->
        <div class="form-section">
            <h3>➕ Nueva Provincia</h3>
            <form method="post" action="AddProvincia.php" class="form-shipping">
                <div class="form-group">
                    <label>Nombre de la Provincia:</label>
                    <input type="text" name="nombre" placeholder="Ej: Lima Metropolitana" required maxlength="100"/>
                </div>
                <button type="submit" class="btn-primary">Agregar Provincia</button>
            </form>
        </div>

        <!-- Tabla Provincias -->
        <?php
        $provincias = [];
        try {
            $stmt = $pdo->query('SELECT id_provincia, nombre FROM provincia ORDER BY nombre ASC');
            $provincias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $provinciasError = $e->getMessage();
        }
        ?>
        <?php if (!empty($provinciasError ?? '')): ?>
            <div class="message error">
                ❌ Error cargando provincias: <?= htmlspecialchars($provinciasError) ?>
            </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table-provincias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($provincias) === 0): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px; color: #666;">
                            No hay provincias registradas
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($provincias as $prov): 
                        $idProv = (int)$prov['id_provincia'];
                        $nombreProv = trim($prov['nombre'] ?? '');
                        if ($nombreProv === '') $nombreProv = 'SIN NOMBRE';
                    ?>
                    <tr>
                        <td class="id-cell"><?= $idProv ?></td>
                        <td><?= htmlspecialchars($nombreProv) ?></td>
                        <td>
                            <button 
                                onclick="editProvincia(<?= $idProv ?>, '<?= htmlspecialchars($nombreProv, ENT_QUOTES) ?>')" 
                                class="edit-btn" title="Editar">
                                ✏️ Editar
                            </button>
                            <form method="post" action="DeleteProvincia.php" style="display:inline;"
                                  onsubmit="return confirm('⚠️ ¿Eliminar provincia \"<?= htmlspecialchars($nombreProv, ENT_QUOTES) ?>\"?')">
                                <input type="hidden" name="id_provincia" value="<?= $idProv ?>" />
                                <button type="submit" class="delete-btn">🗑️ Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tab Precios de Envío -->
    <div id="precios" class="tab-content">
        <!-- Formulario AGREGAR Precio Envío -->
        <div class="form-section">
            <h3>➕ Nuevo Precio de Envío</h3>
            <form method="post" action="AddPrecioEnvio.php" class="form-shipping">
                <div class="form-group">
                    <label>Provincia:</label>
                    <select name="id_provincia" required>
                        <option value="">Seleccionar provincia...</option>
                        <?php foreach ($provincias as $prov): ?>
                            <option value="<?= (int)$prov['id_provincia'] ?>">
                                <?= htmlspecialchars($prov['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Precio de Envío (S/):</label>
                    <input type="number" name="precio_envio" step="0.01" min="0" placeholder="15.50" required/>
                </div>
                <button type="submit" class="btn-primary">Agregar Precio</button>
            </form>
        </div>

        <!-- Tabla Precios -->
        <?php
        $precios = [];
        try {
            $sql = 'SELECT pep.id, pep.precio_envio, p.nombre AS provincia, p.id_provincia
                    FROM precio_envio_provincia pep
                    JOIN provincia p ON pep.id_provincia = p.id_provincia
                    ORDER BY p.nombre';
            $stmt = $pdo->query($sql);
            $precios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $preciosError = $e->getMessage();
        }
        ?>
        <?php if (!empty($preciosError ?? '')): ?>
            <div class="message error">
                ❌ Error cargando precios: <?= htmlspecialchars($preciosError) ?>
            </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table-provincias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Provincia</th>
                        <th>Precio Envío</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($precios) === 0): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #666;">
                            No hay precios de envío configurados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($precios as $row): 
                        $idPrecio  = (int)$row['id'];
                        $provincia = $row['provincia'];
                        $precio    = (float)$row['precio_envio'];
                        $idProv    = (int)$row['id_provincia'];
                    ?>
                    <tr>
                        <td class="id-cell"><?= $idPrecio ?></td>
                        <td><?= htmlspecialchars($provincia) ?></td>
                        <td>S/ <?= number_format($precio, 2, '.', '') ?></td>
                        <td>
                            <button
                                onclick="editPrecioEnvio(<?= $idPrecio ?>, <?= $idProv ?>, '<?= htmlspecialchars($provincia, ENT_QUOTES) ?>', <?= $precio ?>)"
                                class="edit-btn" title="Editar">
                                ✏️ Editar
                            </button>
                            <form method="post" action="DeletePrecioEnvio.php" style="display:inline;"
                                  onsubmit="return confirm('⚠️ ¿Eliminar precio de envío para \"<?= htmlspecialchars($provincia, ENT_QUOTES) ?>\"?')">
                                <input type="hidden" name="id" value="<?= $idPrecio ?>" />
                                <button type="submit" class="delete-btn">🗑️ Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</section>
</main>

<!-- Modal Editar Provincia -->
<div id="modalProvincia" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>✏️ Editar Provincia</h3>
        <form method="post" action="UpdateProvincia.php">
            <input type="hidden" id="editProvId" name="id_provincia"/>
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" id="editProvNombre" name="nombre" required/>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn-primary">Guardar</button>
                <button type="button" class="btn-secondary" onclick="closeModal('modalProvincia')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Precio Envío -->
<div id="modalPrecio" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>✏️ Editar Precio de Envío</h3>
        <form method="post" action="UpdatePrecioEnvio.php">
            <input type="hidden" id="editPrecioId" name="id"/>
            <div class="form-group">
                <label>Provincia:</label>
                <input type="text" id="editPrecioProvincia" readonly/>
            </div>
            <div class="form-group">
                <label>Precio (S/):</label>
                <input type="number" id="editPrecioValor" name="precio_envio" step="0.01" min="0" required/>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn-primary">Guardar</button>
                <button type="button" class="btn-secondary" onclick="closeModal('modalPrecio')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.shipping-tab').forEach(tab => tab.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

function editProvincia(id, nombre) {
    document.getElementById('editProvId').value = id;
    document.getElementById('editProvNombre').value = nombre;
    document.getElementById('modalProvincia').style.display = 'flex';
}

function editPrecioEnvio(id, idProv, provincia, precio) {
    document.getElementById('editPrecioId').value = id;
    document.getElementById('editPrecioProvincia').value = provincia;
    document.getElementById('editPrecioValor').value = precio;
    document.getElementById('modalPrecio').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

window.onclick = function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
};

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
    }
});

setTimeout(() => {
    document.querySelectorAll('.message').forEach(msg => {
        msg.style.transition = 'opacity 0.5s ease';
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 500);
    });
}, 5000);

document.querySelectorAll('.dropdown').forEach(dropdown => {
    dropdown.addEventListener('mouseenter', () => {
        dropdown.querySelector('.dropdown-content').style.display = 'block';
    });
    dropdown.addEventListener('mouseleave', () => {
        dropdown.querySelector('.dropdown-content').style.display = 'none';
    });
});
</script>

</body>
</html>
