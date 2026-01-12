<?php
session_start();

$nombre     = $_POST['nombrePersona']    ?? '';
$apellido   = $_POST['apellidoPersona']  ?? '';
$email      = $_POST['email']            ?? '';
$telefono   = $_POST['telefono']         ?? '';
$direccion  = $_POST['direccionNumero']  ?? '';
$referencia = $_POST['referencia']       ?? '';
$provincia  = $_POST['provincia']        ?? ''; // aquí llega el ID
$distrito   = $_POST['distrito']         ?? '';

$cliente = [
    'nombre'      => $nombre,
    'apellido'    => $apellido,
    'email'       => $email,
    'telefono'    => $telefono,
    'direccion'   => $direccion,
    'referencias' => $referencia,
    'provincia'   => $provincia,  // por ahora guardamos el ID
    'distrito'    => $distrito,
];

$_SESSION['cliente'] = $cliente;

// ----- calcular precio de envío según provincia (puedes mejorar la lógica) -----
$precioEnvioProvincia = 0.00;
$nombreProvincia      = 'Lima';

// si quieres usar la tabla provincia para obtener el nombre
$dsn  = 'mysql:host=localhost;dbname=tiendaropa;charset=utf8';
$user = 'root';
$pass = 'root';

if (ctype_digit((string)$provincia)) {
    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT nombre FROM provincia WHERE id_provincia = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([(int)$provincia]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $nombreProvincia = $row['nombre'];
        }
    } catch (PDOException $e) {
        // si falla, dejamos Lima por defecto
    }
}

// regla simple de ejemplo
if (strcasecmp($nombreProvincia, 'Lima') === 0) {
    $precioEnvioProvincia = 10.00;
} else {
    $precioEnvioProvincia = 20.00;
}

// variables que usará confirmarPedido.php
$provinciaSeleccionada   = $nombreProvincia;
$precioEnvioProvinciaVar = $precioEnvioProvincia;

include 'confirmarPedido.php';
