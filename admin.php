<?php
// admin.php
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

    // Nombre del admin logueado
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

    // Lista de administradores
    $sql = 'SELECT a.ID_admin, a.nombre, a.apellido, a.telefono, a.rol,
                   u.email, u.estado, u.fecha_registro, u.fecha_ultimo_acceso
            FROM administrador a
            JOIN usuario u ON a.ID_usuario = u.ID_usuario';
    $admins = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $nombre = $email;
    $admins = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <title>Administradores - Administración</title>
    <link rel="stylesheet" href="css/admin.css">
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
        <a href="logout.php" title="Cerrar sesión" style="color:white; cursor:pointer;">Cerrar sesión</a>
        <span><?= htmlspecialchars($nombre) ?></span>
        <img src="assets/img/avatar-male.png" alt="avatar" class="avatar-small">
    </div>
</header>

<section class="admin-section">
    <h2>Administradores Registrados</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Teléfono</th>
                <th>Email Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Fecha Registro</th>
                <th>Último Acceso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($admins) === 0): ?>
            <tr><td colspan="9">Error cargando administradores o no hay registros.</td></tr>
        <?php else: ?>
            <?php foreach ($admins as $a): ?>
            <tr>
                <form action="UpdateAdmin.php" method="POST" onsubmit="return confirm('¿Guardar cambios?');">
                    <input type="hidden" name="ID_admin" value="<?= (int)$a['ID_admin'] ?>"/>
                    <input type="hidden" name="email" value="<?= htmlspecialchars($a['email']) ?>"/>
                    <td><input type="text" name="nombre"   value="<?= htmlspecialchars($a['nombre']) ?>"   required/></td>
                    <td><input type="text" name="apellido" value="<?= htmlspecialchars($a['apellido']) ?>" required/></td>
                    <td><input type="text" name="telefono" value="<?= htmlspecialchars($a['telefono']) ?>"/></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
                    <td>
                        <select name="rol">
                            <option value="SuperAdmin"   <?= $a['rol']==='SuperAdmin'   ? 'selected' : '' ?>>SuperAdmin</option>
                            <option value="Administrador"<?= $a['rol']==='Administrador'? 'selected' : '' ?>>Administrador</option>
                            <option value="Moderador"    <?= $a['rol']==='Moderador'    ? 'selected' : '' ?>>Moderador</option>
                        </select>
                    </td>
                    <td>
                        <select name="estado">
                            <option value="Activo"     <?= $a['estado']==='Activo'     ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo"   <?= $a['estado']==='Inactivo'   ? 'selected' : '' ?>>Inactivo</option>
                            <option value="Bloqueado"  <?= $a['estado']==='Bloqueado'  ? 'selected' : '' ?>>Bloqueado</option>
                        </select>
                    </td>
                    <td><?= $a['fecha_registro']      ? htmlspecialchars($a['fecha_registro'])      : 'N/A' ?></td>
                    <td><?= $a['fecha_ultimo_acceso'] ? htmlspecialchars($a['fecha_ultimo_acceso']) : 'N/A' ?></td>
                    <td>
                        <button type="submit">Guardar</button>
                        <a href="DeleteAdmin.php?ID_admin=<?= (int)$a['ID_admin'] ?>"
                           onclick="return confirm('¿Seguro que deseas eliminar este administrador?');"
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
