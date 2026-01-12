<?php
session_start();

// 1. Verificar tipo de usuario
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'Administrador') {
    header('Location: indexAdmin.php');
    exit;
}

$emailSession = $_SESSION['userName'] ?? '';
$nombreAdmin  = '';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=tiendaropa;charset=utf8mb4', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT a.nombre
            FROM administrador a
            JOIN usuario u ON a.ID_usuario = u.ID_usuario
            WHERE u.email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$emailSession]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nombreAdmin = $row['nombre'];
    } else {
        $nombreAdmin = $emailSession;
    }
} catch (Exception $e) {
    $nombreAdmin = $emailSession;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta charset="UTF-8"/>
    <title>Panel de Administración - Home</title>
    <link rel="stylesheet" href="css/home.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<aside class="sidebar">
    <div class="logo">Inventory</div>
    <div class="profile">
        <img src="assets/img/avatar-male.png" alt="avatar" class="avatar">
        <h3><?php echo htmlspecialchars($nombreAdmin ?: 'Admin', ENT_QUOTES, 'UTF-8'); ?></h3>
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
                    <li><a href="shipping.php">Proveedores</a></li>
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
            <div class="notifications-container" id="notificationsContainer">
                <i class="material-icons" id="notificationsIcon">notifications</i>
                <span class="notification-badge" id="notificationBadge" style="display:none;">0</span>
            </div>
            <a href="logout.php" id="logoutBtn" title="Cerrar sesión" style="color:white; cursor:pointer;">Cerrar sesión</a>
            <span><?php echo htmlspecialchars($nombreAdmin, ENT_QUOTES, 'UTF-8'); ?></span>
            <img src="assets/img/avatar-male.png" alt="avatar" class="avatar-small"/>
        </div>
    </header>

    <!-- Modal de Notificaciones -->
    <div id="notificationsModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🔔 Pedidos Pendientes</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body" id="notificationsList">
                <p>Cargando notificaciones...</p>
            </div>
        </div>
    </div>

    <!-- Modal de detalle de pedido -->
    <div id="pedidoModal" class="modal" style="display:none;">
      <div class="modal-content" style="display:block; max-width:700px;">
        <div class="modal-header">
            <h3>Detalle del pedido</h3>
            <span class="close" id="cerrarPedidoModal">&times;</span>
        </div>
        <div class="modal-body" id="detallePedidoHome">
            Cargando...
        </div>
      </div>
    </div>

    <section class="responsive-tiles">
        <h2>RESPONSIVE TILES</h2>
        <div class="tiles-container">
            <div class="tile" id="tileAdministrators">
                <div class="tile-label">Administrators</div>
                <div class="tile-number" id="countAdmins">-</div>
                <div class="tile-icon material-icons">person</div>
            </div>
            <div class="tile" id="tileClients">
                <div class="tile-label">Clients</div>
                <div class="tile-number" id="countClients">-</div>
                <div class="tile-icon material-icons">people</div>
            </div>
            <div class="tile" id="tileProducts">
                <div class="tile-label">Products</div>
                <div class="tile-number" id="countProducts">-</div>
                <div class="tile-icon material-icons">photo_library</div>
            </div>
            <div class="tile" id="tileSales">
                <div class="tile-label">Sales</div>
                <div class="tile-number" id="countSales">-</div>
                <div class="tile-icon material-icons">shopping_cart</div>
            </div>
        </div>
    </section>
            
    <section id="tablaDetalle">
        <h2>Detalles</h2>

        <div id="filtrosSales" class="filtros" style="margin-bottom:15px; display:none;">
            <input type="text" id="filtroNombreCliente" placeholder="Nombre cliente"/>
            <select id="filtroEstadoVenta">
                <option value="">Todos</option>
                <option value="Pendiente">Pendiente</option>
                <option value="Pagado">Pagado</option>
                <option value="Cancelado">Cancelado</option>
            </select>
            <button id="btnFiltrarSales">Filtrar ventas</button>
        </div>

        <div id="filtrosProducts" class="filtros" style="margin-bottom:15px; display:none;">
            <input type="text" id="filtroNombreProducto" placeholder="Nombre producto"/>
            <button id="btnFiltrarProducts">Filtrar productos</button>
        </div>

        <div id="filtrosClients" class="filtros" style="margin-bottom:15px; display:none;">
            <input type="text" id="filtroNombreCliente2" placeholder="Nombre cliente"/>
            <button id="btnFiltrarClients">Filtrar clientes</button>
        </div>

        <div id="filtrosAdministrators" class="filtros" style="margin-bottom:15px; display:none;">
            <input type="text" id="filtroNombreAdmin" placeholder="Nombre administrador"/>
            <button id="btnFiltrarAdministrators">Filtrar administradores</button>
        </div>

        <table id="tablaDatos">
            <thead id="theadDatos"></thead>
            <tbody id="tbodyDatos"></tbody>
        </table>
    </section>
</main>

<script>
function cargarNotificaciones() {
    $.ajax({
        url: "notifications.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            let count = parseInt(data.count) || 0;
            $("#notificationBadge").text(count);
            if (count > 0) $("#notificationBadge").show(); else $("#notificationBadge").hide();

            if (data.notifications && data.notifications.length > 0) {
                let lista = "";
                $.each(data.notifications, function(index, notif) {
                    let total = parseFloat(notif.total || 0).toFixed(2);
                    lista += '<div class="notification-item" data-id="' + (notif.id || '') + '">' +
                        '<strong>' + (notif.nombreCliente || 'Cliente') + '</strong><br/>' +
                        'Pedido #' + (notif.id || '') + ' - ' + (notif.fecha || '') + '<br/>' +
                        'Total: S/ ' + total +
                        '</div>';
                });
                $("#notificationsList").html(lista);
            } else {
                $("#notificationsList").html('<div class="notification-item"><p>✅ No hay pedidos pendientes</p></div>');
            }
        },
        error: function() {
            $("#notificationsList").html('<div class="notification-item"><p>⚠️ Error cargando notificaciones</p></div>');
        }
    });
}

function cargarContadores() {
    $.ajax({
        url: "dashboardData.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            $("#countAdmins").text(data.administrators || 0);
            $("#countClients").text(data.clients || 0);
            $("#countProducts").text(data.products || 0);
            $("#countSales").text(data.sales || 0);
        },
        error: function() {
            $(".tile-number").text("0");
        }
    });
}

function ocultarFiltros() { $(".filtros").hide(); }

function limpiarFiltros(tipo) {
    switch(tipo) {
        case "sales": $("#filtroNombreCliente").val(''); $("#filtroEstadoVenta").val(''); break;
        case "products": $("#filtroNombreProducto").val(''); break;
        case "clients": $("#filtroNombreCliente2").val(''); break;
        case "administrators": $("#filtroNombreAdmin").val(''); break;
    }
}

function cargarDetalles(tipo, filtros) {
    let data = { tipo: tipo };
    switch(tipo) {
        case "sales":
            data.filtroNombreCliente = filtros ? filtros.filtroNombreCliente || '' : '';
            data.filtroEstadoVenta   = filtros ? filtros.filtroEstadoVenta || '' : '';
            break;
        case "products":
            data.filtroNombreProducto = filtros ? filtros.filtroNombreProducto || '' : '';
            break;
        case "clients":
            data.filtroNombreCliente2 = filtros ? filtros.filtroNombreCliente2 || '' : '';
            break;
        case "administrators":
            data.filtroNombreAdmin = filtros ? filtros.filtroNombreAdmin || '' : '';
            break;
    }

    $.ajax({
        url: "dashboardDetails.php",
        method: "GET",
        data: data,
        dataType: "json",
        success: function(data) {
            let thead = "";
            let tbody = "";

            switch(tipo) {
                case "sales":
                    thead = "<tr><th>ID</th><th>ID Cliente</th><th>Nombre Cliente</th><th>Fecha</th><th>Estado</th><th>Total</th></tr>";
                    $.each(data, function(index, item) {
                        tbody += "<tr>" +
                            "<td>" + (item.id || '') + "</td>" +
                            "<td>" + (item.cliente || '') + "</td>" +
                            "<td>" + (item.nombreCliente || '') + "</td>" +
                            "<td>" + (item.fecha || '') + "</td>" +
                            "<td>" + (item.estado || '') + "</td>" +
                            "<td>" + (item.total ? 'S/ ' + parseFloat(item.total).toFixed(2) : '') + "</td>" +
                        "</tr>";
                    });
                    break;
                case "products":
                    thead = "<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th></tr>";
                    $.each(data, function(index, item) {
                        tbody += "<tr>" +
                            "<td>" + (item.id || '') + "</td>" +
                            "<td>" + (item.nombre || '') + "</td>" +
                            "<td>" + (item.precio ? 'S/ ' + parseFloat(item.precio).toFixed(2) : '') + "</td>" +
                            "<td>" + (item.stock || '') + "</td>" +
                        "</tr>";
                    });
                    break;
                case "clients":
                    thead = "<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Teléfono</th></tr>";
                    $.each(data, function(index, item) {
                        tbody += "<tr>" +
                            "<td>" + (item.id || '') + "</td>" +
                            "<td>" + (item.nombre || '') + "</td>" +
                            "<td>" + (item.apellido || '') + "</td>" +
                            "<td>" + (item.telefono || '') + "</td>" +
                        "</tr>";
                    });
                    break;
                case "administrators":
                    thead = "<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Teléfono</th></tr>";
                    $.each(data, function(index, item) {
                        tbody += "<tr>" +
                            "<td>" + (item.id || '') + "</td>" +
                            "<td>" + (item.nombre || '') + "</td>" +
                            "<td>" + (item.apellido || '') + "</td>" +
                            "<td>" + (item.telefono || '') + "</td>" +
                        "</tr>";
                    });
                    break;
            }
            $("#theadDatos").html(thead);
            $("#tbodyDatos").html(tbody);
        }
    });
}

function safeValue(val) {
    if (val === null || val === undefined || val === false) return '-';
    return val;
}

$(document).ready(function() {
    cargarContadores();
    ocultarFiltros();
    cargarNotificaciones();
    setInterval(cargarNotificaciones, 30000);

    $(document).on('click', '#notificationsContainer', function(e) {
        e.stopPropagation();
        $('#notificationsModal .modal-content').show();
        $('#notificationsModal').show();
    });
    
    $(document).on('click', '.close', function() {
        $(this).closest('.modal').hide();
    });
    
    $(document).on('click', '#notificationsModal', function(e) {
        if (e.target.id === 'notificationsModal') {
            $('#notificationsModal').hide();
        }
    });
    
    $(document).on('click', '.tile', function() {
        let tipo = $(this).attr("id").replace("tile", "").toLowerCase();
        ocultarFiltros();
        limpiarFiltros(tipo);
        switch(tipo) {
            case "sales": $("#filtrosSales").show(); break;
            case "products": $("#filtrosProducts").show(); break;
            case "clients": $("#filtrosClients").show(); break;
            case "administrators": $("#filtrosAdministrators").show(); break;
        }
        cargarDetalles(tipo);
    });
    
    $('#btnFiltrarSales').click(function() {
        cargarDetalles("sales", {
            filtroNombreCliente: $("#filtroNombreCliente").val(),
            filtroEstadoVenta: $("#filtroEstadoVenta").val()
        });
    });
    $('#btnFiltrarProducts').click(function() {
        cargarDetalles("products", { filtroNombreProducto: $("#filtroNombreProducto").val() });
    });
    $('#btnFiltrarClients').click(function() {
        cargarDetalles("clients", { filtroNombreCliente2: $("#filtroNombreCliente2").val() });
    });
    $('#btnFiltrarAdministrators').click(function() {
        cargarDetalles("administrators", { filtroNombreAdmin: $("#filtroNombreAdmin").val() });
    });
});

// Click en una notificación -> cargar detalle y abrir modal
$(document).on("click", ".notification-item", function (e) {
    e.stopPropagation();
    var idPedido = $(this).data("id");
    if (!idPedido) return;

    $.ajax({
        url: "pedidoDetail.php",
        method: "GET",
        data: { id: idPedido },
        dataType: "json",
        success: function (data) {
            var html = "<h4>Pedido ID: " + safeValue(data.ID_pedido) + "</h4>" +
                "<p><strong>Fecha:</strong> " + safeValue(data.fecha) + "</p>" +
                "<p><strong>Cliente:</strong> " + safeValue(data.nombre) + " " + safeValue(data.apellido) + "</p>" +
                "<p><strong>Teléfono:</strong> " + safeValue(data.telefono) + "</p>" +
                "<p><strong>Dirección:</strong> " + safeValue(data.direccion) + "</p>" +
                "<p><strong>Método de pago:</strong> " + safeValue(data.metodo_pago) + "</p>" +
                "<p><strong>Costo de envío:</strong> S/ " + safeValue(data.costo_envio) + "</p>" +
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
                    "' target='_blank' style='background:#007bff;color:white;padding:8px 15px;text-decoration:none;border-radius:5px;'>📄 Descargar Boleta</a>" +
                    "</p>";

            $("#detallePedidoHome").html(html);
            $("#pedidoModal").show();
        },
        error: function () {
            $("#detallePedidoHome").html("<p style='color:red;'>❌ Error al cargar detalles del pedido.</p>");
            $("#pedidoModal").show();
        }
    });
});

$(document).on('click', '#pedidoModal', function(e) {
    if (e.target.id === 'pedidoModal') {
        $('#pedidoModal').hide();
    }
});

// Dropdown menus
document.addEventListener("DOMContentLoaded", function() {
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
