<?php
// sales.php
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

$filtroFechaInicio = $_GET['filtroFechaInicio'] ?? null;
$filtroFechaFin    = $_GET['filtroFechaFin'] ?? null;
$filtroEstado      = $_GET['filtroEstado'] ?? null;
$filtroCliente     = $_GET['filtroCliente'] ?? null;
$filtroMetodo      = $_GET['filtroMetodo'] ?? null;

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

    // Query pedidos con filtros
    $sql = "SELECT p.ID_pedido, p.fecha, p.estado, p.total,
                   c.nombre, c.apellido, c.telefono, c.direccion,
                   pa.metodo_pago, pr.imagen AS imagen_producto
            FROM pedido p
            JOIN cliente c ON p.ID_cliente = c.ID_cliente
            LEFT JOIN pago pa ON pa.ID_pedido = p.ID_pedido
            LEFT JOIN detallepedido dp ON dp.ID_pedido = p.ID_pedido
            LEFT JOIN producto pr ON pr.ID_producto = dp.ID_producto
            WHERE 1=1";
    $params = [];

    if ($filtroFechaInicio !== null && trim($filtroFechaInicio) !== '') {
        $sql      .= " AND p.fecha >= ?";
        $params[] = $filtroFechaInicio . " 00:00:00";
    }
    if ($filtroFechaFin !== null && trim($filtroFechaFin) !== '') {
        $sql      .= " AND p.fecha <= ?";
        $params[] = $filtroFechaFin . " 23:59:59";
    }
    if ($filtroEstado !== null && trim($filtroEstado) !== '') {
        $sql      .= " AND p.estado = ?";
        $params[] = $filtroEstado;
    }
    if ($filtroCliente !== null && trim($filtroCliente) !== '') {
        $sql      .= " AND (c.nombre LIKE ? OR c.apellido LIKE ?)";
        $params[] = "%" . $filtroCliente . "%";
        $params[] = "%" . $filtroCliente . "%";
    }
    if ($filtroMetodo !== null && trim($filtroMetodo) !== '') {
        $sql      .= " AND pa.metodo_pago = ?";
        $params[] = $filtroMetodo;
    }
    $sql .= " ORDER BY p.fecha DESC";

    $stmtPedidos = $pdo->prepare($sql);
    $stmtPedidos->execute($params);
    $pedidosRows = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $nombreAdmin = $emailSession;
    $pedidosRows = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Gestión de Pedidos</title>
<link rel="stylesheet" href="css/sales.css" />
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
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
        <img src="assets/img/avatar-male.png" alt="avatar" class="avatar-small" />
    </div>
</header>

<section class="orders-section">
<div id="mensajeActualizacion" style="display:none; text-align:center; font-weight:bold; margin-bottom:10px;"></div>

<form id="formFiltro" method="GET" action="sales.php" style="display:flex; gap:10px; margin-bottom:15px;">
    Fecha inicio: 
    <input type="date" name="filtroFechaInicio" value="<?= htmlspecialchars($filtroFechaInicio ?? '') ?>" />
    Fecha fin: 
    <input type="date" name="filtroFechaFin" value="<?= htmlspecialchars($filtroFechaFin ?? '') ?>" />

    Estado:
    <select name="filtroEstado">
        <?php
        $estados = ["", "Pendiente", "Pagado", "Enviado", "Entregado", "Cancelado", "Reembolsado"];
        $labels  = ["Todos", "Pendiente", "Pagado", "Enviado", "Entregado", "Cancelado", "Reembolsado"];
        foreach ($estados as $i => $val) {
            $sel = ($val === ($filtroEstado ?? '')) ? 'selected' : '';
            echo "<option value=\"".htmlspecialchars($val)."\" $sel>".htmlspecialchars($labels[$i])."</option>";
        }
        ?>
    </select>

    Cliente:
    <input type="text" name="filtroCliente" placeholder="Nombre o Apellido" 
           value="<?= htmlspecialchars($filtroCliente ?? '') ?>" />

    Método pago:
    <select name="filtroMetodo">
        <?php
        $metodos = ["", "yape", "Tarjeta", "Efectivo"];
        $metLabels = ["Todos", "Yape", "Tarjeta", "Efectivo"];
        foreach ($metodos as $i => $val) {
            $sel = ($val === ($filtroMetodo ?? '')) ? 'selected' : '';
            echo "<option value=\"".htmlspecialchars($val)."\" $sel>".htmlspecialchars($metLabels[$i])."</option>";
        }
        ?>
    </select>

    <button type="submit" class="btn-primary">Filtrar</button>
</form>

<table class="orders-table">
<thead>
    <tr>
        <th>Fecha</th>
        <th>Cliente</th>
        <th>Contacto</th>
        <th>Método de pago</th>
        <th>Producto comprado</th>
        <th>Total</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>
</thead>
<tbody>
<?php
$currentPedido = -1;
foreach ($pedidosRows as $row) {
    $pedidoId = (int)$row['ID_pedido'];
    if ($pedidoId !== $currentPedido) {
        $currentPedido = $pedidoId;
        $fecha   = htmlspecialchars($row['fecha'] ?? '');
        $nomCli  = htmlspecialchars(($row['nombre'] ?? '').' '.($row['apellido'] ?? ''));
        $tel     = htmlspecialchars($row['telefono'] ?? '');
        $dir     = htmlspecialchars($row['direccion'] ?? '');
        $metodo  = $row['metodo_pago'] ?? 'Sin pago';
        $metodo  = htmlspecialchars($metodo);
        $imgProd = htmlspecialchars($row['imagen_producto'] ?? '');
        $total   = $row['total'];

        echo "<tr>";
        echo "<form class='form-actualizar-estado' action='UpdateOrder.php' method='POST'>";
        echo "<input type='hidden' name='ID_pedido' value='{$pedidoId}' />";
        echo "<td>{$fecha}</td>";
        echo "<td>{$nomCli}</td>";
        echo "<td>
                <input type='text' name='telefono' value='{$tel}' style='width:80px;' /><br />
                <input type='text' name='direccion' value='{$dir}' style='width:180px;' />
              </td>";
        echo "<td>{$metodo}</td>";
        echo "<td>";
        if (!empty($imgProd)) {
            echo "<img src='images/{$imgProd}' alt='Producto comprado' width='50' height='50' />";
        } else {
            echo "N/A";
        }
        echo "</td>";
        echo "<td>S/{$total}</td>";

        $estadoActual = $row['estado'] ?? 'Pendiente';
        echo "<td><select name='estado'>";
        foreach (["Pendiente","Pagado","Enviado","Entregado","Cancelado","Reembolsado"] as $est) {
            $sel = ($est === $estadoActual) ? 'selected' : '';
            echo "<option value='".htmlspecialchars($est)."' $sel>".htmlspecialchars($est)."</option>";
        }
        echo "</select></td>";

        echo "<td>
                <button type='submit' class='btn-update'>Guardar</button>
                <button type='button' class='btn-detalle' data-id='{$pedidoId}'>Ver detalles</button>
                <button type='button' class='btn-cancelar' data-id='{$pedidoId}'>Cancelar</button>
                <button type='button' class='btn-reembolso' data-id='{$pedidoId}'>Reembolsar</button>
              </td>";
        echo "</form>";
        echo "</tr>";
    }
}
if (empty($pedidosRows)) {
    echo "<tr><td colspan='8'>No hay pedidos para los filtros seleccionados.</td></tr>";
}
?>
</tbody>
</table>

<div id="pedidoModal" style="display:none;position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
  <div style="background: white; margin: 10% auto; padding: 20px; max-width: 600px; border-radius: 8px; position: relative;">
    <span id="cerrarModal" style="position: absolute; top: 10px; right: 20px; cursor: pointer; font-size: 24px;">&times;</span>
    <div id="detallePedido"></div>
  </div>
</div>

<script>
function safeValue(val) {
    if (val === null || val === undefined || val === false) return '-';
    return val;
}

$(document).on("click", ".btn-detalle", function () {
    var idPedido = $(this).data("id");
    $.ajax({
        url: "PedidoDetail.php",
        method: "GET",
        data: { id: idPedido },
        dataType: "json",
        success: function (data) {
            var html = "<h3>Pedido ID: " + safeValue(data.ID_pedido) + "</h3>" +
                "<p><strong>Fecha:</strong> " + safeValue(data.fecha) + "</p>" +
                "<p><strong>Cliente:</strong> " + safeValue(data.nombre) + " " + safeValue(data.apellido) + "</p>" +
                "<p><strong>Teléfono:</strong> " + safeValue(data.telefono) + "</p>" +
                "<p><strong>Dirección:</strong> " + safeValue(data.direccion) + "</p>" +
                "<p><strong>Método de pago:</strong> " + safeValue(data.metodo_pago) + "</p>" +
                "<p><strong>Total:</strong> S/ " + safeValue(data.total) + "</p>" +
                "<p><strong>Estado:</strong> " + safeValue(data.estado) + "</p>";

            if (data.codigo_aprobacion) {
                html += "<p><strong>Número de operación:</strong> " + safeValue(data.codigo_aprobacion) + "</p>";
            }

            if (data.imagen_pago) {
                html += "<p><strong>Comprobante:</strong><br>" +
                        "<img src='comprobantes/" + safeValue(data.imagen_pago) + "' style='max-width:200px;' /></p>";
            }

            if (data.productos && data.productos.length > 0) {
                html += "<h4>Detalle de Productos:</h4>" +
                        "<table border='1' style='width:100%; border-collapse: collapse; font-size:14px;'>" +
                        "<thead style='background:#f0f0f0;'>" +
                        "<tr>" +
                        "<th style='padding:8px;'>Producto</th>" +
                        "<th style='padding:8px;'>Cant.</th>" +
                        "<th style='padding:8px;'>Color</th>" +
                        "<th style='padding:8px;'>Talla</th>" +
                        "<th style='padding:8px;'>P. Unitario</th>" +
                        "<th style='padding:8px;'>Total</th>" +
                        "</tr>" +
                        "</thead><tbody>";

                data.productos.forEach(function(prod) {
                    var subtotal = (prod.preciounitario * prod.cantidad).toFixed(2);
                    html += "<tr>" +
                            "<td style='padding:8px;'>" + safeValue(prod.nombre) + "</td>" +
                            "<td style='padding:8px;text-align:center;'>" + safeValue(prod.cantidad) + "</td>" +
                            "<td style='padding:8px;text-align:center;'><strong>" + safeValue(prod.color) + "</strong></td>" +
                            "<td style='padding:8px;text-align:center;'><strong>" + safeValue(prod.talla) + "</strong></td>" +
                            "<td style='padding:8px;text-align:right;'>S/ " + safeValue(prod.preciounitario) + "</td>" +
                            "<td style='padding:8px;text-align:right;'><strong>S/ " + subtotal + "</strong></td>" +
                            "</tr>";
                });
                html += "</tbody></table>";
            } else {
                html += "<p>No hay productos en este pedido.</p>";
            }

            html += "<p style='margin-top:20px;'>" +
                    "<a href='boletaPDF.php?idPedido=" + safeValue(data.ID_pedido) + 
                    "' target='_blank' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>📄 Descargar PDF</a>" +
                    "</p>";

            $("#detallePedido").html(html);
            $("#pedidoModal").show();
        },
        error: function () {
            $("#detallePedido").html("<p style='color:red;'>❌ Error al cargar detalles del pedido.</p>");
            $("#pedidoModal").show();
        }
    });
});

$("#cerrarModal").click(function () {
    $("#pedidoModal").hide();
});

$(window).click(function(event) {
    if (event.target.id === "pedidoModal") {
        $("#pedidoModal").hide();
    }
});

$(document).on("click", ".btn-cancelar", function () {
    if (confirm("¿Seguro que quieres cancelar este pedido?")) {
        let idPedido = $(this).data("id");
        $.post("CancelarPedido.php", { ID_pedido: idPedido })
            .done(function (response) {
                alert(response);
                location.reload();
            })
            .fail(function (xhr) {
                alert("Error: " + xhr.responseText);
            });
    }
});

$(document).on("click", ".btn-reembolso", function () {
    if (confirm("¿Seguro que quieres reembolsar este pedido?")) {
        let idPedido = $(this).data("id");
        $.post("ReembolsoPedido.php", { ID_pedido: idPedido })
            .done(function (response) {
                alert(response);
                location.reload();
            })
            .fail(function (xhr) {
                alert("Error: " + xhr.responseText);
            });
    }
});

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
</section>
</main>
</body>
</html>
