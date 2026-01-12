<?php
// payments.php
session_start();

$userType = $_SESSION['userType'] ?? null;
if ($userType === null || $userType !== 'Administrador') {
    header('Location: indexAdmin.php');
    exit;
}

$email  = $_SESSION['userName'] ?? '';
$nombre = '';

$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4';
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

    // Cargar métodos de pago
    $metodos = [];
    $stmt = $pdo->query('SELECT * FROM metodo_pago');
    $metodos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $nombre  = $email;
    $metodos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Métodos de Pago - Administración</title>
    <link rel="stylesheet" href="css/payments.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <aside class="sidebar">
        <div class="logo">Inventory</div>
        <div class="profile">
            <img src="assets/img/avatar-male.png" alt="avatar" class="avatar">
            <h3><?= (!empty($nombre) ? htmlspecialchars($nombre) : 'Admin') ?></h3>
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
                <a href="logout.php" style="color:white; cursor:pointer;">Cerrar sesión</a>
                <span><?= htmlspecialchars($nombre) ?></span>
                <img src="assets/img/avatar-male.png" alt="avatar" class="avatar-small">
            </div>
        </header>

        <section class="payment-methods-section">
            <h2>Gestión de Métodos de Pago</h2>
            <br>
            <!-- Agregar método -->
            <form action="AddPaymentMethod.php" method="POST" class="form-method">
                <label>Nombre método:</label>
                <input type="text" name="nombre_metodo" required />
                <label>Descripción:</label>
                <textarea name="descripcion" rows="3"></textarea>
                <label>Activo:</label>
                <select name="activo">
                    <option value="1" selected>Si</option>
                    <option value="0">No</option>
                </select>
                <button type="submit">Agregar método</button>
            </form>

            <div class="methods-list">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($metodos) === 0): ?>
                        <tr><td colspan="4">No hay métodos de pago registrados</td></tr>
                    <?php else: ?>
                        <?php foreach ($metodos as $m): ?>
                        <tr>
                            <form action="EditPaymentMethod.php" method="POST">
                                <input type="hidden" name="ID_metodo" value="<?= (int)$m['ID_metodo'] ?>"/>
                                <td>
                                    <input type="text" name="nombre_metodo" 
                                           value="<?= htmlspecialchars($m['nombre_metodo']) ?>" required/>
                                </td>
                                <td>
                                    <textarea name="descripcion" rows="2"><?= htmlspecialchars($m['descripcion']) ?></textarea>
                                </td>
                                <td>
                                    <select name="activo">
                                        <option value="1" <?= $m['activo'] ? 'selected' : '' ?>>Si</option>
                                        <option value="0" <?= !$m['activo'] ? 'selected' : '' ?>>No</option>
                                    </select>
                                </td>
                                <td><button type="submit">Guardar</button></td>
                            </form>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

<script>
document.getElementById("adminDropdownBtn").addEventListener("click", function(e) {
    e.preventDefault();
    var dropdown = document.getElementById("adminDropdownContent");
    dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
});
window.addEventListener('click', function(event) {
    if (!event.target.closest('#adminDropdownBtn') && !event.target.closest('.dropdown')) {
        var dropdown = document.getElementById("adminDropdownContent");
        if (dropdown) dropdown.style.display = "none";
    }
});

document.getElementById("usersDropdownBtn").addEventListener("click", function(e) {
    e.preventDefault();
    var dropdown = document.getElementById("usersDropdownContent");
    dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
});
window.addEventListener('click', function(event) {
    if (!event.target.closest('#usersDropdownBtn') && !event.target.closest('.dropdown')) {
        var dropdown = document.getElementById("usersDropdownContent");
        if (dropdown) dropdown.style.display = "none";
    }
});
</script>
</body>
</html>
