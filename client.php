<?php
// client.php
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

    // Nombre del admin
    $stmt = $pdo->prepare(
        'SELECT a.nombre 
         FROM administrador a 
         JOIN usuario u ON a.ID_usuario = u.ID_usuario 
         WHERE u.email = ?'
    );
    $stmt->execute([$emailSession]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nombreAdmin = $row['nombre'];
    } else {
        $nombreAdmin = $emailSession;
    }

    // Usuarios (clientes)
    $sql = 'SELECT c.ID_cliente, c.nombre, c.apellido, c.telefono,
                   u.email, u.estado, u.fecha_registro, u.fecha_ultimo_acceso
            FROM cliente c
            JOIN usuario u ON c.ID_usuario = u.ID_usuario';
    $clientes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $nombreAdmin = $emailSession;
    $clientes    = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Usuarios - Administración</title>
    <link rel="stylesheet" href="css/client.css">
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
        <a href="logout.php" title="Cerrar sesión" style="color:white; cursor:pointer;">Cerrar sesión</a>
        <span><?= htmlspecialchars($nombreAdmin) ?></span>
        <img src="assets/img/avatar-male.png" alt="avatar" class="avatar-small">
    </div>
</header>

<section class="user-section">
    <h2>Usuarios Registrados</h2>
    <table class="user-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Teléfono</th>
                <th>Email Usuario</th>
                <th>Estado</th>
                <th>Fecha Registro</th>
                <th>Último Acceso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($clientes) === 0): ?>
            <tr><td colspan="8">Error cargando usuarios o no hay registros.</td></tr>
        <?php else: ?>
            <?php foreach ($clientes as $c): ?>
            <tr>
                <form action="UpdateUser.php" method="POST" onsubmit="return confirm('¿Guardar cambios?');">
                    <input type="hidden" name="ID_cliente" value="<?= (int)$c['ID_cliente'] ?>"/>
                    <input type="hidden" name="email" value="<?= htmlspecialchars($c['email']) ?>"/>
                    <td><input type="text" name="nombre"   value="<?= htmlspecialchars($c['nombre']) ?>"   required/></td>
                    <td><input type="text" name="apellido" value="<?= htmlspecialchars($c['apellido']) ?>" required/></td>
                    <td><input type="text" name="telefono" value="<?= htmlspecialchars($c['telefono']) ?>"/></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td>
                        <select name="estado">
                            <option value="Activo"    <?= $c['estado']==='Activo'    ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo"  <?= $c['estado']==='Inactivo'  ? 'selected' : '' ?>>Inactivo</option>
                            <option value="Bloqueado" <?= $c['estado']==='Bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                        </select>
                    </td>
                    <td><?= $c['fecha_registro']      ? htmlspecialchars($c['fecha_registro'])      : 'N/A' ?></td>
                    <td><?= $c['fecha_ultimo_acceso'] ? htmlspecialchars($c['fecha_ultimo_acceso']) : 'N/A' ?></td>
                    <td>
                        <button type="submit">Guardar</button>
                        <a href="DeleteUser.php?ID_cliente=<?= (int)$c['ID_cliente'] ?>"
                           onclick="return confirm('¿Seguro que deseas eliminar este usuario?');"
                           class="btn-delete">Eliminar</a>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
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
